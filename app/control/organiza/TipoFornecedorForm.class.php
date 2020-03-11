<?php
/**
 * ConveniadasForm Form
 * @author  MARCOS RODRIGO JUNG ALVES
 */
 class TipoFornecedorForm extends TPage
 {
     protected $form; // form

     /**
      * Form constructor
      * @param $param Request
      */
     public function __construct( $param )
     {
         parent::__construct();

         // creates the form
         $this->form = new TQuickForm('form_TipoFornecedor');
         $this->form->class = 'tform'; // change CSS class
         $this->form = new BootstrapFormWrapper($this->form);
         $this->form->style = 'display: table;width:100%'; // change style

         // define the form title
         $this->form->setFormTitle('TipoFornecedor');



         // create the form fields
         $id = new THidden('id');
         $descricao = new TEntry('descricao');


         // add the fields
         $this->form->addQuickField('Id', $id,  '50%' );
         $this->form->addQuickField('Descricao', $descricao,  '100%' , new TRequiredValidator);




         if (!empty($id))
         {
             $id->setEditable(FALSE);
         }

         /** samples
          $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
          $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
          $fieldX->setSize( 100, 40 ); // set size
          **/

         // create the form actions
         $btn = $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
         $btn->class = 'btn btn-sm btn-primary';
         $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');
         $this->form->addQuickAction(_t('List'),  new TAction(array('TipoFornecedorList', 'onReload')), 'bs:list blue blue');

         // vertical box container
         $container = new TVBox;
         $container->style = 'width: 90%';
         // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
         $container->add(TPanelGroup::pack('CADASTRO: TIPO DE FORNECEDOR', $this->form));

         parent::add($container);
     }

     /**
      * Save form data
      * @param $param Request
      */
     public function onSave( $param )
     {
         try
         {
             TTransaction::open('organizamc'); // open a transaction


             // Enable Debug logger for SQL operations inside the transaction
             TTransaction::setLogger(new TLoggerSTD); // standard output
             TTransaction::setLogger(new TLoggerTXT('log.txt')); // file


             $this->form->validate(); // validate form data

             $object = new TipoFornecedor;  // create an empty object
             $data = $this->form->getData(); // get form data as array
             $object->fromArray( (array) $data); // load the object with data
             $object->descricao = strtoupper($object->descricao);
             $object->store(); // save the object

             // get the generated id
             $data->id = $object->id;

             $this->form->setData($data); // fill form data
             TTransaction::close(); // close the transaction

             new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
         }
         catch (Exception $e) // in case of exception
         {
             new TMessage('error', $e->getMessage()); // shows the exception error message
             $this->form->setData( $this->form->getData() ); // keep form data
             TTransaction::rollback(); // undo all pending operations
         }
     }

     /**
      * Clear form data
      * @param $param Request
      */
     public function onClear( $param )
     {
         $this->form->clear(TRUE);
     }

     /**
      * Load object to form data
      * @param $param Request
      */
     public function onEdit( $param )
     {
         try
         {
             if (isset($param['key']))
             {
                 $key = $param['key'];  // get the parameter $key
                 TTransaction::open('organizamc'); // open a transaction
                 $object = new TipoFornecedor($key); // instantiates the Active Record
                 $this->form->setData($object); // fill the form
                 TTransaction::close(); // close the transaction
             }
             else
             {
                 $this->form->clear(TRUE);
             }
         }
         catch (Exception $e) // in case of exception
         {
             new TMessage('error', $e->getMessage()); // shows the exception error message
             TTransaction::rollback(); // undo all pending operations
         }
     }
 }
