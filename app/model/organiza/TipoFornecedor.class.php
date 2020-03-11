<?php
/**
 * Casamento Active Record
 * @author  Marcos Rodrigo Jung Alves
 */
class TipoFornecedor extends TRecord
{
    const TABLENAME = 'tipofornecedor';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}


    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
    }
}
