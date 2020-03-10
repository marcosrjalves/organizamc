<?php
/**
 * Locais Active Record
 * @author  Marcos Rodrigo Jung Alves
 */
class Locais extends TRecord
{
    const TABLENAME = 'locais';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $nome_cidade;
    private $nome_estado;


    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('rua');
        parent::addAttribute('bairro');
        parent::addAttribute('municipio');
        parent::addAttribute('estado');
        parent::addAttribute('numero');
        parent::addAttribute('ativo');
        parent::addAttribute('aprovado');
    }



    public function get_nome_cidade()
    {
        if (empty($this->nome_cidade))
        {
            $this->nome_cidade = new Cidades( $this->municipio );
        }
        return $this->nome_cidade;
    }


    public function get_nome_estado()
    {
        if (empty($this->nome_estado))
        {
            $this->nome_estado = new Estados( $this->estado );
        }
        return $this->nome_estado;
    }


}
