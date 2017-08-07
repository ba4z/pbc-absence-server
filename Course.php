<?php
/**
 * Created by Bas van Klaarbergen
 * User: MacHD
 */
 
class Course {

    private $id;
    private $name;
    private $description;
    private $abbrv;
    private $status;

    function __construct($XmlCourseChild)
    {
        $this->id = $XmlCourseChild->instanceid;
        $this->name = $XmlCourseChild->name;
        $this->description = $XmlCourseChild->description;
        $this->abbrv = $XmlCourseChild->abbrv;
        $this->status = $XmlCourseChild->status;
    }

    public function getAbbrv()
    {
        return $this->abbrv;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    function __toString()
    {
        return "Id:" .  $this->getId() . ", Name: " . $this->getName() . ", Abbrv: " . $this->getAbbrv() . ", Status: " . $this->status;
    }


}
