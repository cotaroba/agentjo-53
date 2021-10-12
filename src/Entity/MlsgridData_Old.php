<?php
/**
 * Created by PhpStorm.
 * User: cotaroba2
 * Date: 9/28/18
 * Time: 09:54
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Hmlsdata_Temp
 *
 * @ORM\Table(name="mlsGridData_Old", uniqueConstraints={@ORM\UniqueConstraint(name="hmlsData_mlsNumber_uindex", columns={"mlsNumber"})}, indexes={@ORM\Index(name="hmlsData_listingAgentId1_index", columns={"listingAgentId1"}), @ORM\Index(name="hmlsData_subPropertyType_index", columns={"bookingSection"}), @ORM\Index(name="hmlsData_city_index", columns={"city"}), @ORM\Index(name="hmlsData_schoolsElementary_index", columns={"elementarySchool"}), @ORM\Index(name="hmlsData_schoolsMiddle_index", columns={"middleSchool"}), @ORM\Index(name="hmlsData_schoolsHigh_index", columns={"highSchool"}), @ORM\Index(name="hmlsData_listPrice_index", columns={"listPrice"}), @ORM\Index(name="hmlsData_dateModified_index", columns={"modTimestamp"}), @ORM\Index(name="hmlsData_listingOfficeId_index", columns={"listingOfficeId"}), @ORM\Index(name="hmlsData_zip_index", columns={"zip"}), @ORM\Index(name="hmlsData_subDivision_index", columns={"subDivision"})})
 * @ORM\Entity
 */
class MlsgridData_Old extends MlsgridData
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

}