<?php
/**
 * Cidades Active Record
 * @author  Marcos Rodrigo Jung Alves
 */
class Cidades extends TRecord
{
    const TABLENAME = 'cidades';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $nome_estado;


    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('uf');
    }


    public function get_nome_estado()
    {
        if (empty($this->nome_estado))
        {
            $this->nome_estado = new Estados( $this->uf );
        }
        return $this->nome_estado;
    }


}
