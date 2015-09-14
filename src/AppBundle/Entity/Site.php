<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Site
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SiteRepository")
 */
class Site
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="seoDescription", type="text")
     */
    private $seoDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="seoKeywords", type="text")
     */
    private $seoKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="xmlParseUrl", type="string", length=255)
     */
    private $xmlParseUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="deliveryUrl", type="string", length=255)
     */
    private $deliveryUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="paymentUrl", type="string", length=255)
     */
    private $paymentUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=255)
     */
    private $alias;

    /**
     * @ORM\Column(name="lastParseDate", type="datetime")
     */
    protected $lastParseDate;

    /**
     * @ORM\Column(name="updatePeriod", type="integer")
     */
    protected $updatePeriod;

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
     * Set title
     *
     * @param string $title
     * @return Site
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Site
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
     * Set xmlParseUrl
     *
     * @param string $xmlParseUrl
     * @return Site
     */
    public function setXmlParseUrl($xmlParseUrl)
    {
        $this->xmlParseUrl = $xmlParseUrl;

        return $this;
    }

    /**
     * Get xmlParseUrl
     *
     * @return string 
     */
    public function getXmlParseUrl()
    {
        return $this->xmlParseUrl;
    }

    /**
     * Set deliveryUrl
     *
     * @param string $deliveryUrl
     * @return Site
     */
    public function setDeliveryUrl($deliveryUrl)
    {
        $this->deliveryUrl = $deliveryUrl;

        return $this;
    }

    /**
     * Get deliveryUrl
     *
     * @return string 
     */
    public function getDeliveryUrl()
    {
        return $this->deliveryUrl;
    }

    /**
     * Set paymentUrl
     *
     * @param string $paymentUrl
     * @return Site
     */
    public function setPaymentUrl($paymentUrl)
    {
        $this->paymentUrl = $paymentUrl;

        return $this;
    }

    /**
     * Get paymentUrl
     *
     * @return string 
     */
    public function getPaymentUrl()
    {
        return $this->paymentUrl;
    }

    /**
     * Set alias
     *
     * @param string $alias
     * @return Site
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
     * Set url
     *
     * @param string $url
     * @return Site
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set seoDescription
     *
     * @param string $seoDescription
     * @return Site
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
     * @return Site
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
     * Set lastParseDate
     *
     * @param \DateTime $lastParseDate
     * @return Site
     */
    public function setLastParseDate($lastParseDate)
    {
        $this->lastParseDate = $lastParseDate;

        return $this;
    }

    /**
     * Get lastParseDate
     *
     * @return \DateTime 
     */
    public function getLastParseDate()
    {
        return $this->lastParseDate;
    }

    /**
     * Set updatePeriod
     *
     * @param integer $updatePeriod
     * @return Site
     */
    public function setUpdatePeriod($updatePeriod)
    {
        $this->updatePeriod = $updatePeriod;

        return $this;
    }

    /**
     * Get updatePeriod
     *
     * @return integer 
     */
    public function getUpdatePeriod()
    {
        return $this->updatePeriod;
    }
}
