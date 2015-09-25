<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CategoryRepository")
 */
class Category
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Site", inversedBy="categories", cascade={"persist"})
     * @ORM\JoinColumn(name="siteId", referencedColumnName="id")
     **/
    private $site;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ExternalCategory", mappedBy="internalParentCategory")
     **/
    private $externalCategories;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="seoDescription", type="text", nullable=true)
     */
    private $seoDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="seoKeywords", type="text", nullable=true)
     */
    private $seoKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=255, nullable=true)
     */
    private $alias;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ourChoice", type="boolean", nullable=true)
     */
    private $ourChoice;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->externalCategories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     *
     * @return string String Category
     */
    public function __toString()
    {
        return $this->getName() ? $this->getName() : 'Новая категория';
    }


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
     * Set name
     *
     * @param string $name
     * @return Category
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
     * Set description
     *
     * @param string $description
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set seoDescription
     *
     * @param string $seoDescription
     * @return Category
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * Get seoDescription
     *
     * @return string 
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * Set seoKeywords
     *
     * @param string $seoKeywords
     * @return Category
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;

        return $this;
    }

    /**
     * Get seoKeywords
     *
     * @return string 
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * Set site
     *
     * @param \AppBundle\Entity\Site $site
     * @return Category
     */
    public function setSite(\AppBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \AppBundle\Entity\Site 
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Add externalCategories
     *
     * @param \AppBundle\Entity\ExternalCategory $externalCategories
     * @return Category
     */
    public function addExternalCategory(\AppBundle\Entity\ExternalCategory $externalCategories)
    {
        $this->externalCategories[] = $externalCategories;

        return $this;
    }

    /**
     * Remove externalCategories
     *
     * @param \AppBundle\Entity\ExternalCategory $externalCategories
     */
    public function removeExternalCategory(\AppBundle\Entity\ExternalCategory $externalCategories)
    {
        $this->externalCategories->removeElement($externalCategories);
    }

    /**
     * Get externalCategories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExternalCategories()
    {
        return $this->externalCategories;
    }

    /**
     * Set alias
     *
     * @param string $alias
     * @return Category
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string 
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set ourChoice
     *
     * @param boolean $ourChoice
     * @return Category
     */
    public function setOurChoice($ourChoice)
    {
        $this->ourChoice = $ourChoice;

        return $this;
    }

    /**
     * Get ourChoice
     *
     * @return boolean 
     */
    public function getOurChoice()
    {
        return $this->ourChoice;
    }
}
