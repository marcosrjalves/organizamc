<?php
/**
 * SystemUserFormForUsers Form
 * @author  <your name here>
 */
class SystemUserFormForUsers extends TPage
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
        $this->form = new TQuickForm('form_SystemUser');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style

        // define the form title
        $this->form->setFormTitle('SystemUser');



        // create the form fields
        $id = new THidden('id');
        $name = new TEntry('name');
        $login = new TEntry('login');
        $password = new TPassword('password');
        $repassword = new TPassword('repassword');
        $email = new TEntry('email');
        $frontpage_id = new THidden('frontpage_id');
        $system_unit_id = new THidden('system_unit_id');
        $active = new THidden('active');


        // add the fields
        $this->form->addQuickField('Id', $id,  '100%' );
        $this->form->addQuickField('Nome completo', $name,  '100%', new TRequiredValidator);
        $this->form->addQuickField('Usuário para Login', $login,  '100%', new TRequiredValidator);
        $this->form->addQuickField('Senha', $password,  '100%', new TRequiredValidator);
        $this->form->addQuickField('Redigite sua Senha', $repassword,  '100%', new TRequiredValidator);
        $this->form->addQuickField('Email', $email,  '100%', new TEmailValidator);
        $this->form->addQuickField('Frontpage Id', $frontpage_id,  '50%' );
        $this->form->addQuickField('System Unit Id', $system_unit_id,  '50%' );
        $this->form->addQuickField('Active', $active,  '100%' );




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
        // $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('CADASTRE-SE, É GRÁTIS!', $this->form));

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
            TTransaction::open('permission'); // open a transaction


            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file


            $this->form->validate(); // validate form data

            $object = new SystemUser;  // create an empty object
            $data = $this->form->getData(); // get form data as array

            $criteriaLogin = new TCriteria;
            $criteriaMail = new TCriteria;
            $respositoryLogin = new TRepository('SystemUser');
            $respositoryMail = new TRepository('SystemUser');
            $criteriaLogin->add(new TFilter('login', 'like', $data->login));
            $criteriaMail->add(new TFilter('email', 'like', $data->email));
            $checklogin = $respositoryLogin->load($criteriaLogin);
            $checkmail = $respositoryMail->load($criteriaMail);
            if ($checklogin[0])
            {
                throw new Exception('Já existe um outro usuário com este mesmo nome de login. Se for o seu, por favor, tente acessar novamente ou entre em contato pelo email: ti@organizaeventos.net para recuperar sua senha');
            }
            else if ($checkmail[0])
            {
                throw new Exception('Já existe um outro usuário com este mesmo email. Se for o seu, por favor, tente acessar novamente ou entre em contato pelo email: ti@organizaeventos.net para recuperar sua senha');
            }
            else
            {
                if ($data->password == $data->repassword)
                {
                    $object->fromArray( (array) $data); // load the object with data
                    $object->password = md5($object->password);
                    $object->frontpage_id = 7;
                    $object->system_unit_id = null;
                    $object->active = 'Y';
                    // $this->form->addQuickField('Frontpage Id', $frontpage_id,  '50%' );
                    // $this->form->addQuickField('System Unit Id', $system_unit_id,  '50%' );
                    // $this->form->addQuickField('Active', $active,  '100%' );
                    $object->store(); // save the object

                    // get the generated id
                    $data->id = $object->id;
                    $systemUserGroup = new SystemUserGroup;
                    $systemUserGroup->system_user_id = $object->id;
                    $systemUserGroup->system_group_id = 2;
                    $systemUserGroup->store();

                    $this->form->setData($data); // fill form data
                    TTransaction::close(); // close the transaction

                    // new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));                  
                    AdiantiCoreApplication::loadPage('LoginForm', 'newUserMessage');
                }
                else
                {
                    throw new Exception('As senhas não coincidem, tente novamente');
                }
            }
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
                TTransaction::open('permission'); // open a transaction
                $object = new SystemUser($key); // instantiates the Active Record
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
