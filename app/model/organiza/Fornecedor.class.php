<?php
/**
 * Fornecedor Active Record
 * @author  Marcos Rodrigo Jung Alves
 */
class Fornecedor extends TRecord
{
    const TABLENAME = 'fornecedor';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}


    private $tipo_fornecedor_descricao;
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
        parent::addAttribute('numero');
        parent::addAttribute('bairro');
        parent::addAttribute('municipio');
        parent::addAttribute('estado');
        parent::addAttribute('avaliacao');
        parent::addAttribute('tipo');
        parent::addAttribute('ativo');
        parent::addAttribute('aprovado');
    }

    public function get_tipo_fornecedor_descricao()
    {
        if (empty($this->tipo_fornecedor_descricao))
        {
            $this->tipo_fornecedor_descricao = new TipoFornecedor( $this->tipo );
        }
        return $this->tipo_fornecedor_descricao;
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

    public function ativar()
    {
        $this->ativo = 'S';
        return $this->ativo;
    }


    public function desativar()
    {
        $this->ativo = 'N';
        return $this->ativo;
    }



}
