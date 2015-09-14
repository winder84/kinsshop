<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExternalCategory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ExternalCategoryRepository")
 */
class ExternalCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="externalId", type="string", length=255)
     */
    private $externalId;

    /**
     * @var integer
     *
     * @ORM\Column(name="internalParentId", type="integer", length=255)
     */
    private $internalParentId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category", inversedBy="externalCategories")
     * @ORM\JoinColumn(name="internalParentId", referencedColumnName="id")
     **/
    private $internalParentCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="parentId", type="string", length=255)
     */
    private $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="version", type="float")
     */
    private $version;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set externalId
     *
     * @param string $externalId
     * @return ExternalCategory
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get externalId
     *
     * @return string 
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set parentId
     *
     * @param string $parentId
     * @return ExternalCategory
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return string 
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ExternalCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set version
     *
     * @param float $version
     * @return ExternalCategory
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return float 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set internalParentId
     *
     * @param integer $internalParentId
     * @return ExternalCategory
     */
    public function setInternalParentId($internalParentId)
    {
        $this->internalParentId = $internalParentId;

        return $this;
    }

    /**
     * Get internalParentId
     *
     * @return integer 
     */
    public function getInternalParentId()
    {
        return $this->internalParentId;
    }

    /**
     * Set internalParentCategory
     *
     * @param \AppBundle\Entity\Category $internalParentCategory
     * @return ExternalCategory
     */
    public function setInternalParentCategory(\AppBundle\Entity\Category $internalParentCategory = null)
    {
        $this->internalParentCategory = $internalParentCategory;

        return $this;
    }

    /**
     * Get internalParentCategory
     *
     * @return \AppBundle\Entity\Category 
     */
    public function getInternalParentCategory()
    {
        return $this->internalParentCategory;
    }
}
