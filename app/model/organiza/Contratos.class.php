<?php
/**
 * Casamento Active Record
 * @author  Marcos Rodrigo Jung Alves
 */
class Contratos extends TRecord
{
    const TABLENAME = 'contratos';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $tipo_fornecedor_descricao;
    private $nome_fornecedor_nome;
    private $dados_casamento;
    private $user_nome;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_usuario');
        parent::addAttribute('id_casamento');
        parent::addAttribute('tipo_fornecedor');
        parent::addAttribute('nome_fornecedor');
        parent::addAttribute('valor');
        parent::addAttribute('obs');
    }


    public function get_tipo_fornecedor_descricao()
    {
        if (empty($this->tipo_fornecedor_descricao))
        {
            $this->tipo_fornecedor_descricao = new TipoFornecedor( $this->tipo_fornecedor );
        }
        return $this->tipo_fornecedor_descricao->descricao;
    }


    public function get_nome_fornecedor_nome()
    {
        if (empty($this->nome_fornecedor_nome))
        {
            $this->nome_fornecedor_nome = new Fornecedor( $this->nome_fornecedor );
        }
        return $this->nome_fornecedor_nome->nome;
    }


    public function get_nome_noiva_casamento()
    {
        if (empty($this->nome_noiva_casamento))
        {
            $this->nome_noiva_casamento = new Casamento( $this->id_casamento );
        }
        return $this->nome_noiva_casamento->noiva;
    }


    public function get_dados_casamento()
    {
        if (empty($this->dados_casamento))
        {
            $this->dados_casamento = new Casamento( $this->id_casamento );
        }
        return $this->dados_casamento;
    }


    public function get_user_nome()
    {
        if (empty($this->user_nome))
        {
            TTransaction::open('permission'); // open transaction tributos
            $this->user_nome = new SystemUser( $this->id_usuario );
            TTransaction::close();
        }
        return $this->user_nome;
    }




}
