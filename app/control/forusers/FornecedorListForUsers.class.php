<?php
/**
 * FornecedorList Listing
 * @author  Marcos Rodrigo Jung Alves
 */
class FornecedorListForUsers extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new TQuickForm('form_search_Fornecedor');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Fornecedor');


        // create the form fields
        $nome = new TEntry('nome');
        $rua = new TEntry('rua');
        $numero = new TEntry('numero');
        $bairro = new TEntry('bairro');
        $municipio = new TDBCombo('municipio', 'organizamc', 'Cidades', 'id', 'nome', 'nome');
        $estado = new TDBCombo('estado', 'organizamc', 'Estados', 'id', 'uf', 'uf');
        $avaliacao = new TEntry('avaliacao');
        $tipo = new TDBCombo('tipo_fornecedor', 'organizamc', 'TipoFornecedor', 'id', 'descricao', 'descricao');


        // add the fields
        $this->form->addQuickField('Nome', $nome,  '100%' );
        $this->form->addQuickField('Rua', $rua,  '100%' );
        $this->form->addQuickField('Numero', $numero,  '100%' );
        $this->form->addQuickField('Bairro', $bairro,  '100%' );
        $this->form->addQuickField('Municipio', $municipio,  '100%' );
        $this->form->addQuickField('Estado', $estado,  '100%' );
        $this->form->addQuickField('Avaliacao', $avaliacao,  '100%' );
        $this->form->addQuickField('Tipo', $tipo,  '100%' );


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Fornecedor_filter_data') );

        // add the search form actions
        $btn = $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array('FornecedorFormForUsers', 'onEdit')), 'bs:plus-sign green');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_rua = new TDataGridColumn('rua', 'Rua', 'left');
        $column_numero = new TDataGridColumn('numero', 'Numero', 'left');
        $column_bairro = new TDataGridColumn('bairro', 'Bairro', 'left');
        $column_municipio = new TDataGridColumn('nome_cidade->nome', 'Municipio', 'left');
        $column_estado = new TDataGridColumn('nome_estado->uf', 'Estado', 'left');
        // $column_avaliacao = new TDataGridColumn('avaliacao', 'Avaliacao', 'right');
        $column_tipo = new TDataGridColumn('tipo_fornecedor_descricao->descricao', 'Tipo', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_rua);
        $this->datagrid->addColumn($column_numero);
        $this->datagrid->addColumn($column_bairro);
        $this->datagrid->addColumn($column_municipio);
        $this->datagrid->addColumn($column_estado);
        // $this->datagrid->addColumn($column_avaliacao);
        $this->datagrid->addColumn($column_tipo);


        // create EDIT action
        $action_edit = new TDataGridAction(array('FornecedorFormForUsers', 'onEdit'));
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());



        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('LISTA DE FORNECEDORES', $this->form));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

        parent::add($container);
    }

    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];

            TTransaction::open('organizamc'); // open a transaction with database
            $object = new Fornecedor($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction

            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();

        // clear session filters
        TSession::setValue('FornecedorList_filter_nome',   NULL);
        TSession::setValue('FornecedorList_filter_rua',   NULL);
        TSession::setValue('FornecedorList_filter_numero',   NULL);
        TSession::setValue('FornecedorList_filter_bairro',   NULL);
        TSession::setValue('FornecedorList_filter_municipio',   NULL);
        TSession::setValue('FornecedorList_filter_estado',   NULL);
        TSession::setValue('FornecedorList_filter_avaliacao',   NULL);
        TSession::setValue('FornecedorList_filter_tipo',   NULL);

        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue('FornecedorList_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->rua) AND ($data->rua)) {
            $filter = new TFilter('rua', 'like', "%{$data->rua}%"); // create the filter
            TSession::setValue('FornecedorList_filter_rua',   $filter); // stores the filter in the session
        }


        if (isset($data->numero) AND ($data->numero)) {
            $filter = new TFilter('numero', 'like', "%{$data->numero}%"); // create the filter
            TSession::setValue('FornecedorList_filter_numero',   $filter); // stores the filter in the session
        }


        if (isset($data->bairro) AND ($data->bairro)) {
            $filter = new TFilter('bairro', 'like', "%{$data->bairro}%"); // create the filter
            TSession::setValue('FornecedorList_filter_bairro',   $filter); // stores the filter in the session
        }


        if (isset($data->municipio) AND ($data->municipio)) {
            $filter = new TFilter('municipio', 'like', "%{$data->municipio}%"); // create the filter
            TSession::setValue('FornecedorList_filter_municipio',   $filter); // stores the filter in the session
        }


        if (isset($data->estado) AND ($data->estado)) {
            $filter = new TFilter('estado', 'like', "%{$data->estado}%"); // create the filter
            TSession::setValue('FornecedorList_filter_estado',   $filter); // stores the filter in the session
        }


        if (isset($data->avaliacao) AND ($data->avaliacao)) {
            $filter = new TFilter('avaliacao', 'like', "%{$data->avaliacao}%"); // create the filter
            TSession::setValue('FornecedorList_filter_avaliacao',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo) AND ($data->tipo)) {
            $filter = new TFilter('tipo', 'like', "%{$data->tipo}%"); // create the filter
            TSession::setValue('FornecedorList_filter_tipo',   $filter); // stores the filter in the session
        }


        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue('Fornecedor_filter_data', $data);

        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }

    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'organizamc'
            TTransaction::open('organizamc');

            // creates a repository for Fornecedor
            $repository = new TRepository('Fornecedor');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;

            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);


            if (TSession::getValue('FornecedorList_filter_nome')) {
                $criteria->add(TSession::getValue('FornecedorList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('FornecedorList_filter_rua')) {
                $criteria->add(TSession::getValue('FornecedorList_filter_rua')); // add the session filter
            }


            if (TSession::getValue('FornecedorList_filter_numero')) {
                $criteria->add(TSession::getValue('FornecedorList_filter_numero')); // add the session filter
            }


            if (TSession::getValue('FornecedorList_filter_bairro')) {
                $criteria->add(TSession::getValue('FornecedorList_filter_bairro')); // add the session filter
            }


            if (TSession::getValue('FornecedorList_filter_municipio')) {
                $criteria->add(TSession::getValue('FornecedorList_filter_municipio')); // add the session filter
            }


            if (TSession::getValue('FornecedorList_filter_estado')) {
                $criteria->add(TSession::getValue('FornecedorList_filter_estado')); // add the session filter
            }


            if (TSession::getValue('FornecedorList_filter_avaliacao')) {
                $criteria->add(TSession::getValue('FornecedorList_filter_avaliacao')); // add the session filter
            }


            if (TSession::getValue('FornecedorList_filter_tipo')) {
                $criteria->add(TSession::getValue('FornecedorList_filter_tipo')); // add the session filter
            }

            $criteria->add(new TFilter('ativo', 'like', 'S'));


            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }

            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }

            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Ask before deletion
     */
    public function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead

        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }

    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('organizamc'); // open a transaction with database
            $object = new Fornecedor($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }





    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
