<?php
/**
 * Casamento Active Record
 * @author  Marcos Rodrigo Jung Alves
 */
class Casamento extends TRecord
{
    const TABLENAME = 'casamento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $Casamentos;
    private $local_cerimonia_nome;
    private $local_recepcao_nome;
    private $user_nome;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_usuario');
        parent::addAttribute('noiva');
        parent::addAttribute('noivo');
        parent::addAttribute('dia');
        parent::addAttribute('hora');
        parent::addAttribute('orcamento');
        parent::addAttribute('local_cerimonia');
        parent::addAttribute('local_recepcao');
        parent::addAttribute('obs');
    }

    //use: $column_situacao = new TDataGridColumn('situacao_convenios->descricao', 'Situacao', 'right');
    // public function get_situacao_convenios()
    // {
    //     if (empty($this->situacao_convenios))
    //     {
    //         $this->situacao_convenios = new Situacoes( $this->situacao );
    //     }
    //     return $this->situacao_convenios;
    // }
    public function get_local_cerimonia_nome()
    {
        if (empty($this->local_cerimonia_nome))
        {
            $this->local_cerimonia_nome = new Locais( $this->local_cerimonia );
        }
        return $this->local_cerimonia_nome;
    }


    public function get_local_recepcao_nome()
    {
        if (empty($this->local_recepcao_nome))
        {
            $this->local_recepcao_nome = new Locais( $this->local_recepcao );
        }
        return $this->local_recepcao_nome;
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


    // Load
    public function loadbyuser($id, $id_user)
    {
        $respository = new TRepository('Casamento');
        $criteria = new TCriteria;
        $criteria->add(new TFilder('$id_user', '=', $id_user));

        return parent;
    }

    // public function load($id)
    // {
    //
    //     // load the related Movimentacoes objects
    //     $repository = new TRepository('Movimentacoes');
    //     $criteria = new TCriteria;
    //     $criteria->add(new TFilter('conv_id', '=', $id));
    //     $this->Casamentos = $repository->load($criteria);
    //
    //     // load the object itself
    //     return $this->Casamentos;
    // }

    /**
     * Store the object and its aggregates
     */
    // public function store()
    // {
    //     // store the object itself
    //     parent::store();
    //
    //     // delete the related Movimentacoes objects
    //     $criteria = new TCriteria;
    //     $criteria->add(new TFilter('conv_id', '=', $this->id));
    //     $repository = new TRepository('Movimentacoes');
    //     $repository->delete($criteria);
    //     // store the related Movimentacoes objects
    //     if ($this->Movimentacoess)
    //     {
    //         foreach ($this->Movimentacoess as $Movimentacoes)
    //         {
    //             unset($Movimentacoes->id);
    //             $Movimentacoes->conv_id = $this->id;
    //             $Movimentacoes->store();
    //         }
    //     }
    // }


}
