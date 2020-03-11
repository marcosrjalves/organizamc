<?php
/**
 * Estados Active Record
 * @author  Marcos Rodrigo Jung Alves
 */
class Estados extends TRecord
{
    const TABLENAME = 'estados';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}


    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('uf');
    }


}
