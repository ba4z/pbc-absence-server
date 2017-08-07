<?php
/**
 * Created by IntelliJ IDEA.
 * User: MacHD
 * Date: 9/5/12
 * Time: 14:35 
 * To change this template use File | Settings | File Templates.
 */
 
class Term {

    private $id;
    private $name;
    private $fullName;
    private $start;
    private $end;

    function __construct($xmlTerm)
    {
        $this->id = $xmlTerm->termid;
        $this->name = $xmlTerm->name;
        $this->fullName = $xmlTerm->fullname;
        $this->start = $xmlTerm->start_date;
        $this->end = $xmlTerm->end_date;
    }

    //Getters
    
    public function getEnd()
    {
        return $this->end;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getFullName()
    {
        return $this->fullName;
    }

    function __toString()
    {
        return "Id: "  . $this->getId() . ", Name: " . $this->getName() . ", Start: " . $this->getStart() . ", End: " . $this->getEnd();
    }


}
