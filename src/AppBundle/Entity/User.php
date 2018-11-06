<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="email", message="This email address is already in use")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $email;



    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="text")
     */
    private $firstname;



    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="text")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $role;

    /**
     * @Assert\Length(max=4096)
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="altEmail", type="text", nullable=true)
     */
    private $alternativeEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="tel", type="text", nullable=true)
     */
    private $tel;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="text", nullable=true)
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="text", nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="adress", type="text", nullable=true)
     */
    private $Adress;

    /**
     * @var string
     *
     * @ORM\Column(name="companyName", type="text", nullable=true)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="text", nullable=true)
     */
    private $logo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $yearEstablished;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="text", nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="business_type", type="text", nullable=true)
     */
    private $businessType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $numberEmp;

    /**
     * @var string
     *
     * @ORM\Column(name="reg_adress", type="text", nullable=true)
     */
    private $regAdress;

    /**
     * @var string
     *
     * @ORM\Column(name="opt_adress", type="text", nullable=true)
     */
    private $optAdress;

    /**
     * @var string
     *
     * @ORM\Column(name="about_us", type="text", nullable=true)
     */
    private $aboutUs;

    /**
     * @var string
     *
     * @ORM\Column(name="security_question", type="text", nullable=true)
     */
    private $secuityQuestion;

    /**
     * @var string
     *
     * @ORM\Column(name="response", type="text", nullable=true)
     */
    private $response;

    /**
     * @var string
     *
     * @ORM\Column(name="stripeId", type="text", nullable=true)
     */
    private $stripeId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="text", nullable=true)
     */
    private $facebook;

    /**
     * @var string
     *
     * @ORM\Column(name="twitter", type="text", nullable=true)
     */
    private $twitter;

    /**
     * @var string
     *
     * @ORM\Column(name="linkedin", type="text", nullable=true)
     */
    private $linkedin;

    /**
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param string $facebook
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param string $twitter
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * @return string
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * @param string $linkedin
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;
    }


    /**
     * @return string
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * @param string $stripeId
     */
    public function setStripeId($stripeId)
    {
        $this->stripeId = $stripeId;
    }



    /**
     * @return string
     */
    public function getSecuityQuestion()
    {
        return $this->secuityQuestion;
    }

    /**
     * @param string $secuityQuestion
     */
    public function setSecuityQuestion($secuityQuestion)
    {
        $this->secuityQuestion = $secuityQuestion;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }



    /**
     * @return string
     */
    public function getAdress()
    {
        return $this->Adress;
    }

    /**
     * @param string $Adress
     */
    public function setAdress($Adress)
    {
        $this->Adress = $Adress;
    }



    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return mixed
     */
    public function getYearEstablished()
    {
        return $this->yearEstablished;
    }

    /**
     * @param mixed $yearEstablished
     */
    public function setYearEstablished($yearEstablished)
    {
        $this->yearEstablished = $yearEstablished;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getBusinessType()
    {
        return $this->businessType;
    }

    /**
     * @param string $businessType
     */
    public function setBusinessType($businessType)
    {
        $this->businessType = $businessType;
    }

    /**
     * @return mixed
     */
    public function getNumberEmp()
    {
        return $this->numberEmp;
    }

    /**
     * @param mixed $numberEmp
     */
    public function setNumberEmp($numberEmp)
    {
        $this->numberEmp = $numberEmp;
    }

    /**
     * @return string
     */
    public function getRegAdress()
    {
        return $this->regAdress;
    }

    /**
     * @param string $regAdress
     */
    public function setRegAdress($regAdress)
    {
        $this->regAdress = $regAdress;
    }

    /**
     * @return string
     */
    public function getOptAdress()
    {
        return $this->optAdress;
    }

    /**
     * @param string $optAdress
     */
    public function setOptAdress($optAdress)
    {
        $this->optAdress = $optAdress;
    }

    /**
     * @return string
     */
    public function getAboutUs()
    {
        return $this->aboutUs;
    }

    /**
     * @param string $aboutUs
     */
    public function setAboutUs($aboutUs)
    {
        $this->aboutUs = $aboutUs;
    }



    /**
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * @param string $tel
     */
    public function setTel($tel)
    {
        $this->tel = $tel;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }




    /**
     * @return string
     */
    public function getAlternativeEmail()
    {
        return $this->alternativeEmail;
    }

    /**
     * @param string $alternativeEmail
     */
    public function setAlternativeEmail($alternativeEmail)
    {
        $this->alternativeEmail = $alternativeEmail;
    }



    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }


    public function eraseCredentials()
    {
        return null;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role = null)
    {
        $this->role = $role;
    }

    public function getRoles()
    {
        return [$this->getRole()];
    }

    public function getId()
    {
        return $this->id;
    }



    public function getUsername()
    {
        return $this->email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    public function getSalt()
    {
        return null;
    }
}