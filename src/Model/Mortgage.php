<?php

namespace App\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PropertyTaxRates;
use Doctrine\ORM\Query;
use Doctrine\ORM\ORMException;
use Http\Client\Exception;
use App\Entity\MortgageRate;

class Mortgage
{

    protected $utility;
    protected $em;
    protected $api;

    public function __construct(Utility $utility, EntityManagerInterface $em)
    {
        $this->utility = $utility;
        $this->em = $em;
        $this->api = $this->utility->getParameter('quandlApi');
    }

    public function getMortgage_fromListing($listprice, $county, $returnJson = false,$annual_taxes = 0){
        if ($annual_taxes === 0 || $annual_taxes === null) { $annual_taxes = round($this->getTaxRate($county) * $listprice / 100, 2);}
        return $this->getMortgage($listprice, 0, 30, $this->getMortgageRate(),
            $annual_taxes, 0, $returnJson);
    }

    public function getMortgage_paginationItems(array $items){
        $mortgageArray = [];
        if (!is_array($items)) {
            return $mortgageArray;
        }
        foreach($items as $key=>$val){
            $mortgageArray[$val['mlsnumber']]=$this->getMortgage_fromListing($val['listprice'], $val['county'], false,$val['taxes']);
        }
        return $mortgageArray;
    }

    public function getMortgage($price, $down = 0, $term = 30, $rate =0, $annual_taxes = 0, $annual_insurance = 0, $returnJson = false)
    {
        $defaults = $this->utility->getParameter('mortgage');
        $mortgage['listprice'] = $price;
        $mortgage['downPayment'] = ($down == 0) ? round($price * 0.2) : $down;
        $mortgage['term'] = ($term == 0) ? 30 : $term;
        $mortgage['interestRate'] = ($rate == 0) ? $this->getMortgageRate() : $rate;
        $mortgage['principleInterest'] = $this->getPrincipleInterest_monthly($price, $mortgage['downPayment'],
            $mortgage['term'], $mortgage['interestRate']);
        $mortgage['annualTaxes'] = ($annual_taxes == 0) ? round( $price / 100, 2) : $annual_taxes;
        $mortgage['annualInsurance'] = ($annual_insurance == 0) ? round($price * $defaults['defaultHomeInsurance'], 2) : $annual_insurance;
        $mortgage['monthlyPayment'] = round($mortgage['principleInterest'] + ($mortgage['annualInsurance'] / 12) +
            ($mortgage['annualTaxes'] / 12), 2);


        return ($returnJson === false) ? $mortgage : json_encode($mortgage);

    }

    public function setMortgageRate()
    {
        /*
         * Using API on quandl.com (username: andre.cota-robles@oakcrestgroup.com)
         * 30-YR Freddie MAC US Average Interest Rate. Save to DataBase.
         */
        $guzzle = new Client();
        $dateEnd = date('Y-m-d');
        $dateStart = date('Y-m-d', strtotime("-1 week"));
        try {
            $rateApi = $guzzle->request('GET',
                'https://www.quandl.com/api/v3/datasets/FMAC/30US.json?start_date=' .
                $dateStart . '&end_date=' . $dateEnd . '&api_key=' . $this->api);
            if ($rateApi->getReasonPhrase() !== 'OK') {
                return $rateApi->getReasonPhrase();
            }
            $rateData = json_decode((string)$rateApi->getBody(), JSON_OBJECT_AS_ARRAY);
            $em = $this->em;
            $mortgageRate = new mortgageRate();
            if (array_key_exists("dataset", $mortgageRate)) {
                $mortgageRate->setRate($rateData['dataset']['data']['0']['1']);
                $mortgageRate->setDateUpdated($rateData['dataset']['refreshed_at']);
                $mortgageRate->setDetails(json_encode($rateData['dataset']));
                $em->persist($mortgageRate);
                $em->flush();
                $em->clear();
                return 'Database Update OK. Rate: ' . $rateData['dataset']['data']['0']['1'] . '%.  Date: ' . $rateData['dataset']['refreshed_at'] . '.  END.';
            }
            else {
                echo "\nRate not Updated";
            }
        }
        catch (ConnectException $e){
            return $e->getMessage();
        }
    }

    private function getPrincipleInterest_monthly($price, $down, $term, $rate = 5)
    {

        $i = $rate / 100 / 12;
        $n = $term * 12;
        $payment = (($price - $down) * $i * pow((1 + $i), $n)) / (pow((1 + $i), $n) - 1);
        return round($payment, 2);
    }

    private function getMortgageRate()
    {
        $em = $this->em;
        $defaults = $this->utility->getParameter('mortgage');
        $results = $em->createQueryBuilder()
            ->select('m')
            ->from('App:MortgageRate', 'm')
            ->orderBy('m.dateUpdated', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        if (key_exists('0', $results)){
            return $results[0]['rate'];
        } else {
            return $defaults['defaultInterestRate'];
        }
    }

    private function getTaxRate($county)
    {
        $em = $this->em;
        $query = $em->createQueryBuilder()
            ->select('m.taxRate')
            ->from('App:PropertyTaxRates', 'm')
            ->where('m.county = :county')
            ->setParameter('county', $county)
            ->getQuery();
        try {
            $results = $query->getResult(Query::HYDRATE_SINGLE_SCALAR);
            return $results;
        } catch (ORMException $e) {
            return 1;
        }
    }
}
