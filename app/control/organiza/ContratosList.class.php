<?php
/**
 * ContratosList Listing
 * @author  Marcos Rodrigo Jung Alves
 */
class ContratosList extends TPage
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
        $this->form = new TQuickForm('form_search_Contratos');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Contratos');


        // create the form fields
        // $tipo = new TDBCombo('tipo', 'organizamc', 'TipoFornecedor', 'id', 'descricao');
        // field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
        $id = new TEntry('id');
        $id_casamentonoiva = new TDBUniqueSearch('id_casamentonoiva', 'organizamc', 'Casamento', 'id', 'noiva');
        $id_casamentonoivo = new TDBUniqueSearch('id_casamentonoivo', 'organizamc', 'Casamento', 'id', 'noivo');
        $id_usuario = new TDBUniqueSearch('id_usuario', 'permission', 'SystemUser', 'id', 'name');
        $tipo_fornecedor = new TDBCombo('tipo_fornecedor', 'organizamc', 'TipoFornecedor', 'id', 'descricao', 'descricao');
        $nome_fornecedor = new TDBUniqueSearch('nome_fornecedor', 'organizamc', 'Fornecedor', 'id', 'nome');
        $valor = new TEntry('valor');
        // $obs = new TEntry('obs');


        // add the fields
        $this->form->addQuickField('Id', $id,  '100%' );
        $this->form->addQuickField('Nome da Noiva', $id_casamentonoiva,  '100%' );
        $this->form->addQuickField('Nome do Noivo', $id_casamentonoivo,  '100%' );
        $this->form->addQuickField('Id Usuario', $id_usuario,  '100%' );
        $this->form->addQuickField('Tipo Fornecedor', $tipo_fornecedor,  '100%' );
        $this->form->addQuickField('Nome Fornecedor', $nome_fornecedor,  '100%' );
        $this->form->addQuickField('Valor', $valor,  '100%' );
        // $this->form->addQuickField('Obs', $obs,  '100%' );


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Contratos_filter_data') );

        // add the search form actions
        $btn = $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array('ContratosForm', 'onEdit')), 'bs:plus-sign green');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_id_casamento = new TDataGridColumn('dados_casamento->noiva', 'Noiva', 'right');
        $column_id_usuario = new TDataGridColumn('user_nome->name', 'Usuário', 'right');
        $column_tipo_fornecedor = new TDataGridColumn('tipo_fornecedor_descricao', 'Tipo Fornecedor', 'right');
        $column_nome_fornecedor = new TDataGridColumn('nome_fornecedor_nome', 'Nome Fornecedor', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_obs = new TDataGridColumn('obs', 'Obs', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_id_casamento);
        $this->datagrid->addColumn($column_id_usuario);
        $this->datagrid->addColumn($column_tipo_fornecedor);
        $this->datagrid->addColumn($column_nome_fornecedor);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_obs);

        $column_valor->setTransformer( function($value, $object, $row) {
            return 'R$ ' . number_format($value, 2, ',', '.');
            // return 'R$ ' . $value;
        });


        $column_valor->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });


        // create EDIT action
        $action_edit = new TDataGridAction(array('ContratosForm', 'onEdit'));
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
        $container->add(TPanelGroup::pack('LISTAGEM DE CONTRATOS', $this->form));
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
            $object = new Contratos($key); // instantiates the Active Record
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
        TSession::setValue('ContratosList_filter_id',   NULL);
        TSession::setValue('ContratosList_filter_id_casamentonoiva',   NULL);
        TSession::setValue('ContratosList_filter_id_casamentonoivo',   NULL);
        TSession::setValue('ContratosList_filter_id_usuario',   NULL);
        TSession::setValue('ContratosList_filter_tipo_fornecedor',   NULL);
        TSession::setValue('ContratosList_filter_nome_fornecedor',   NULL);
        TSession::setValue('ContratosList_filter_valor',   NULL);
        TSession::setValue('ContratosList_filter_obs',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('ContratosList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->id_casamentonoiva) AND ($data->id_casamentonoiva)) {
            $filter = new TFilter('id_casamento', 'like', "%{$data->id_casamentonoiva}%"); // create the filter
            TSession::setValue('ContratosList_filter_id_casamentonoiva',   $filter); // stores the filter in the session
        }

        if (isset($data->id_casamentonoivo) AND ($data->id_casamentonoivo)) {
            $filter = new TFilter('id_casamento', 'like', "%{$data->id_casamentonoivo}%"); // create the filter
            TSession::setValue('ContratosList_filter_id_casamentonoivo',   $filter); // stores the filter in the session
        }


        if (isset($data->id_usuario) AND ($data->id_usuario)) {
            $filter = new TFilter('id_usuario', 'like', "%{$data->id_usuario}%"); // create the filter
            TSession::setValue('ContratosList_filter_id_usuario',   $filter); // stores the filter in the session
        }


        if (isset($data->tipo_fornecedor) AND ($data->tipo_fornecedor)) {
            $filter = new TFilter('tipo_fornecedor', 'like', "%{$data->tipo_fornecedor}%"); // create the filter
            TSession::setValue('ContratosList_filter_tipo_fornecedor',   $filter); // stores the filter in the session
        }


        if (isset($data->nome_fornecedor) AND ($data->nome_fornecedor)) {
            $filter = new TFilter('nome_fornecedor', 'like', "%{$data->nome_fornecedor}%"); // create the filter
            TSession::setValue('ContratosList_filter_nome_fornecedor',   $filter); // stores the filter in the session
        }


        if (isset($data->valor) AND ($data->valor)) {
            $filter = new TFilter('valor', 'like', "%{$data->valor}%"); // create the filter
            TSession::setValue('ContratosList_filter_valor',   $filter); // stores the filter in the session
        }


        if (isset($data->obs) AND ($data->obs)) {
            $filter = new TFilter('obs', 'like', "%{$data->obs}%"); // create the filter
            TSession::setValue('ContratosList_filter_obs',   $filter); // stores the filter in the session
        }


        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue('Contratos_filter_data', $data);

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

            // creates a repository for Contratos
            $repository = new TRepository('Contratos');
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


            if (TSession::getValue('ContratosList_filter_id')) {
                $criteria->add(TSession::getValue('ContratosList_filter_id')); // add the session filter
            }


            if (TSession::getValue('ContratosList_filter_id_casamento')) {
                $criteria->add(TSession::getValue('ContratosList_filter_id_casamento')); // add the session filter
            }


            if (TSession::getValue('ContratosList_filter_id_usuario')) {
                $criteria->add(TSession::getValue('ContratosList_filter_id_usuario')); // add the session filter
            }


            if (TSession::getValue('ContratosList_filter_tipo_fornecedor')) {
                $criteria->add(TSession::getValue('ContratosList_filter_tipo_fornecedor')); // add the session filter
            }


            if (TSession::getValue('ContratosList_filter_nome_fornecedor')) {
                $criteria->add(TSession::getValue('ContratosList_filter_nome_fornecedor')); // add the session filter
            }


            if (TSession::getValue('ContratosList_filter_valor')) {
                $criteria->add(TSession::getValue('ContratosList_filter_valor')); // add the session filter
            }


            if (TSession::getValue('ContratosList_filter_obs')) {
                $criteria->add(TSession::getValue('ContratosList_filter_obs')); // add the session filter
            }


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
            $object = new Contratos($key, FALSE); // instantiates the Active Record
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
