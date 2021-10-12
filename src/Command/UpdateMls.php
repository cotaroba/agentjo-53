<?php
/*
 Commands in order for complete update:
    --update-mls
    --download-photo-new
    --set-geodata-all
    --swap-database
    --clean-photos-dir
    --clean-geodata
    --find-missing-photos
*/
namespace App\Command;

use App\Model\GeoData;
use App\Model\Update;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;


class UpdateMls extends Command
{
    protected $geo;
    protected $update;

    public function __construct(Update $update, GeoData $geo)
    {
        $this->update = $update;
        $this->geo = $geo;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:mlsgrid')
            ->setDescription('Updates the MLS Grid databases.')
            ->setHelp('This command updates the MLS database/')

            ->addOption('update-mls', null, InputOption::VALUE_NONE,
                'Update MLS Grid Data')
            ->addOption('download-photo-new', null, InputOption::VALUE_NONE,
                'Update MLS Photo Data New Photos')
            ->addOption('download-photo-all', null, InputOption::VALUE_NONE,
                'Update MLS Photo Data New Photos')
            ->addOption('find-missing-photos', null, InputOption::VALUE_NONE,
                'Update HMLS Data; Get Missing photos')
            ->addOption('clean-photos-dir', null, InputOption::VALUE_NONE,
                'Clean removed photos out of photos directory')
            ->addOption('set-geodata-all', null, InputOption::VALUE_NONE,
                'Get Geo Coordinates of all addresses')
            ->addOption('clean-geodata', null, InputOption::VALUE_NONE,
                'Get Geo Coordinates of all addresses')
            ->addOption('swap-database', null, InputOption::VALUE_NONE,
                'Swap databases.')
            ->addOption('download-photo-mls', null, InputOption::VALUE_REQUIRED,
                'Download Photos by MLS',false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updateHmls = [];
        $update_mls = $input->getOption('update-mls');
        $download_photo_new  = $input->getOption('download-photo-new');
        $download_photo_all  = $input->getOption('download-photo-all');
        $find_missing_photos = $input->getOption('find-missing-photos');
        $clean_photos_dir = $input->getOption('clean-photos-dir');
        $set_geoData_all = $input->getOption('set-geodata-all');
        $clean_geoData = $input->getOption('clean-geodata');
        $swapDatabase = $input->getOption('swap-database');
        $download_photo_mls = $input->getOption('download-photo-mls');

        $output->writeln("Script: updateHmls: Started at " . date("Y-m-d H:i:s"));
        $output->writeln('x-----x-----x-----x-----x-----x');
        $output->writeln('Start Memory: '. round(memory_get_usage()/1048576, 2) . ' MB');

        if ($update_mls){
            $updateHmls=array_merge($updateHmls, $this->update->updateMls());
        }
        if ($download_photo_new){
            $updateHmls=array_merge($updateHmls, $this->update->downloadPhotos());
        }
        if ($swapDatabase){
            $updateHmls=array_merge($updateHmls, $this->update->swapTempMlsTable());
        }
        if ($clean_photos_dir){
            $updateHmls=array_merge($updateHmls, $this->update->cleanPhotoDir());
        }
        if ($find_missing_photos){
            $updateHmls=array_merge($updateHmls, $this->update->downloadPhotos_missing());
        }
        if ($download_photo_mls){
            $updateHmls=array_merge($updateHmls, $this->update->downloadPhotos_mlsnumber($download_photo_mls));
        }
        if ($download_photo_all){
            $updateHmls=array_merge($updateHmls, $this->update->downloadPhotos('all'));
        }
        if ($set_geoData_all){
            $updateHmls=array_merge($updateHmls, $this->geo->setGeoData_all());
        }
        if ($clean_geoData){
            $updateHmls=array_merge($updateHmls, $this->geo->cleanGeoData());
        }

        if (is_array($updateHmls)){
            foreach ($updateHmls as $key=>$val){
                if (is_array($val)){
                    foreach ($val as $k2=>$v2){
                        $output->writeln($k2 . ': ' . $v2);
                    }
                } else {
                    $output->writeln($key . ': ' . $val);
                }
            }
        }

        $output->writeln('Memory Peak: '. round(memory_get_peak_usage()/1048576,2) . ' MB');
        $output->writeln('x-----x-----x-----x-----x-----x');
        $output->writeln("Script: updateHmls: Ended at " . date("Y-m-d H:i:s"));
        $output->writeln(' ');

        return Command::SUCCESS;
    }


}
