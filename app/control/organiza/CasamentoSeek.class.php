<?php
/**
 * CasamentoSeek Listing
 * @author  Marcos Rodrigo Jung Alves
 */
class CasamentoSeek extends TWindow
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        parent::setTitle( AdiantiCoreTranslator::translate('Search record') );
        parent::setSize(0.7, 750);

        // creates the form
        $this->form = new TQuickForm('form_search_Casamento');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Casamento');


        // create the form fields
        $noiva = new TEntry('noiva');
        $noivo = new TEntry('noivo');
        $dia = new TEntry('dia');
        $hora = new TEntry('hora');
        $orcamento = new TEntry('orcamento');
        $local_cerimonia = new TEntry('local_cerimonia');
        $local_recepcao = new TEntry('local_recepcao');


        // add the fields
        $this->form->addQuickField('Noiva', $noiva,  '100%' );
        $this->form->addQuickField('Noivo', $noivo,  '100%' );
        $this->form->addQuickField('Dia', $dia,  '100%' );
        $this->form->addQuickField('Hora', $hora,  '100%' );
        $this->form->addQuickField('Orcamento', $orcamento,  '100%' );
        $this->form->addQuickField('Local Cerimonia', $local_cerimonia,  '100%' );
        $this->form->addQuickField('Local Recepcao', $local_recepcao,  '100%' );


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Casamento_filter_data') );

        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_id_usuario = new TDataGridColumn('id_usuario', 'Id Usuario', 'right');
        $column_noiva = new TDataGridColumn('noiva', 'Noiva', 'left');
        $column_noivo = new TDataGridColumn('noivo', 'Noivo', 'left');
        $column_dia = new TDataGridColumn('dia', 'Dia', 'left');
        $column_orcamento = new TDataGridColumn('orcamento', 'Orcamento', 'right');
        $column_local_cerimonia = new TDataGridColumn('local_cerimonia_nome->nome', 'Local Cerimonia', 'right');
        $column_local_recepcao = new TDataGridColumn('local_recepcao_nome->nome', 'Local Recepcao', 'right');
        $column_obs = new TDataGridColumn('obs', 'Obs', 'left');
        $column_hora = new TDataGridColumn('hora', 'Hora', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_id_usuario);
        $this->datagrid->addColumn($column_noiva);
        $this->datagrid->addColumn($column_noivo);
        $this->datagrid->addColumn($column_dia);
        $this->datagrid->addColumn($column_orcamento);
        $this->datagrid->addColumn($column_local_cerimonia);
        $this->datagrid->addColumn($column_local_recepcao);
        $this->datagrid->addColumn($column_obs);
        $this->datagrid->addColumn($column_hora);


        // create EDIT action
        $action_select = new TDataGridAction(array('ContratosForm', 'onEdit'));
        $action_select->setUseButton(TRUE);
        $action_select->setLabel('Ok');
        $action_select->setImage('fa:hand-pointer-o green');
        $action_select->setField('id');
        $this->datagrid->addAction($action_select);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(TPanelGroup::pack('Title', $this->form));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

        parent::add($container);
    }

    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();

        // clear session filters
        TSession::setValue('CasamentoSeek_filter_noiva',   NULL);
        TSession::setValue('CasamentoSeek_filter_noivo',   NULL);
        TSession::setValue('CasamentoSeek_filter_dia',   NULL);
        TSession::setValue('CasamentoSeek_filter_hora',   NULL);
        TSession::setValue('CasamentoSeek_filter_orcamento',   NULL);
        TSession::setValue('CasamentoSeek_filter_local_cerimonia',   NULL);
        TSession::setValue('CasamentoSeek_filter_local_recepcao',   NULL);

        if (isset($data->noiva) AND ($data->noiva)) {
            $filter = new TFilter('noiva', 'like', "%{$data->noiva}%"); // create the filter
            TSession::setValue('CasamentoSeek_filter_noiva',   $filter); // stores the filter in the session
        }


        if (isset($data->noivo) AND ($data->noivo)) {
            $filter = new TFilter('noivo', 'like', "%{$data->noivo}%"); // create the filter
            TSession::setValue('CasamentoSeek_filter_noivo',   $filter); // stores the filter in the session
        }


        if (isset($data->dia) AND ($data->dia)) {
            $filter = new TFilter('dia', 'like', "%{$data->dia}%"); // create the filter
            TSession::setValue('CasamentoSeek_filter_dia',   $filter); // stores the filter in the session
        }


        if (isset($data->hora) AND ($data->hora)) {
            $filter = new TFilter('hora', 'like', "%{$data->hora}%"); // create the filter
            TSession::setValue('CasamentoSeek_filter_hora',   $filter); // stores the filter in the session
        }


        if (isset($data->orcamento) AND ($data->orcamento)) {
            $filter = new TFilter('orcamento', 'like', "%{$data->orcamento}%"); // create the filter
            TSession::setValue('CasamentoSeek_filter_orcamento',   $filter); // stores the filter in the session
        }


        if (isset($data->local_cerimonia) AND ($data->local_cerimonia)) {
            $filter = new TFilter('local_cerimonia', 'like', "%{$data->local_cerimonia}%"); // create the filter
            TSession::setValue('CasamentoSeek_filter_local_cerimonia',   $filter); // stores the filter in the session
        }


        if (isset($data->local_recepcao) AND ($data->local_recepcao)) {
            $filter = new TFilter('local_recepcao', 'like', "%{$data->local_recepcao}%"); // create the filter
            TSession::setValue('CasamentoSeek_filter_local_recepcao',   $filter); // stores the filter in the session
        }


        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue('Casamento_filter_data', $data);

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

            // creates a repository for Casamento
            $repository = new TRepository('Casamento');
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


            if (TSession::getValue('CasamentoSeek_filter_noiva')) {
                $criteria->add(TSession::getValue('CasamentoSeek_filter_noiva')); // add the session filter
            }


            if (TSession::getValue('CasamentoSeek_filter_noivo')) {
                $criteria->add(TSession::getValue('CasamentoSeek_filter_noivo')); // add the session filter
            }


            if (TSession::getValue('CasamentoSeek_filter_dia')) {
                $criteria->add(TSession::getValue('CasamentoSeek_filter_dia')); // add the session filter
            }


            if (TSession::getValue('CasamentoSeek_filter_hora')) {
                $criteria->add(TSession::getValue('CasamentoSeek_filter_hora')); // add the session filter
            }


            if (TSession::getValue('CasamentoSeek_filter_orcamento')) {
                $criteria->add(TSession::getValue('CasamentoSeek_filter_orcamento')); // add the session filter
            }


            if (TSession::getValue('CasamentoSeek_filter_local_cerimonia')) {
                $criteria->add(TSession::getValue('CasamentoSeek_filter_local_cerimonia')); // add the session filter
            }


            if (TSession::getValue('CasamentoSeek_filter_local_recepcao')) {
                $criteria->add(TSession::getValue('CasamentoSeek_filter_local_recepcao')); // add the session filter
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
     * Executed when the user chooses the record
     */
    public static function onSelect($param)
    {
        try
        {
            $key = $param['key'];
            TTransaction::open('organizamc');

            // load the active record
            $object = Casamento::find($key);

            // closes the transaction
            TTransaction::close();

            $send = new StdClass;
            $send->casamento_id = $object->id;
            TForm::sendData('ContratosForm', $send);

            parent::closeWindow(); // closes the window
        }
        catch (Exception $e)
        {
            $send = new StdClass;
            $send->casamento_id = '';
            TForm::sendData('ContratosForm', $send);

            // undo pending operations
            TTransaction::rollback();
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
