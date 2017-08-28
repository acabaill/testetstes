<?php
class Omnisense extends Module{
	function __construct()
	{
		$this->name = 'Omnisense';
		$this->tab = 'analytics_stats';
		$this->version = '1.0';
		$this->author = 'Omnisense';
		$this->bootstrap = true;

		parent::__construct();
		$this->displayName = $this->l('Omnisense');
		$this->description = $this->l('Votre solution de réengagement cross platform.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
	     if(parent::install()==false || !$this->registerHook('Header')){
			 return false;
	     }
	     return true;
	}

	public function uninstall()
 	{
 	 	if (!parent::uninstall())
 	 		return false;
		return true;
 	}

//Configuration page
public function displayForm()
	{
		// Get default language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		$helper = new HelperForm();
		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;
		$helper->toolbar_scroll = true;
	 	$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);
		$fields_form = array();
		// Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('API_key'),
					'name' => 'key_api',
					'required' => true,
					'hint' => $this->l('Entrer votre API_key Omnisense')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Identifiant'),
					'name' => 'identifier',
					'required' => true,
					'hint' => $this->l('Entrer votre identifiant Omnisense')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Conserver les information utilisateur?'),
					'name' => 'keep_info',
					'hint' => $this->l('Si un utilisateur supprime une donnée, doit-on les supprimée ?
										Nous conseillons de désactiver cette option par défaut.'),
					'values'    => array(
						array(
							'id' => 'keep_enabled',
							'value' => 1,
							'label' => $this->l('Activer')
						),
						array(
							'id' => 'keep_disabled',
							'value' => 0,
							'label' => $this->l('Desactiver (option par défaut)')
						),
					),
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
			)
		);
		$helper->fields_value['key_api'] = Configuration::get('key_api');
		$helper->fields_value['identifier'] = Configuration::get('identifier');
		$helper->fields_value['keep_info'] = Configuration::get('keep_info');
		return $helper->generateForm($fields_form);
	}

//Get api_key and identifier from Configuration page
	public function getContent()
	{
		$output = '';
		if (Tools::isSubmit('submit'.$this->name))
		{
			$key = Tools::getValue('key_api');
			if (!empty($key))
			{
				Configuration::updateValue('key_api', $key);
				$output .= $this->displayConfirmation($this->l('API_key enregistré avec succée'));
			}
			$id = Tools::getValue('identifier');
			if (!empty($id))
			{
				Configuration::updateValue('identifier', $id);
				$output .= $this->displayConfirmation($this->l('Identifiant enregistré avec succée'));
			}
			$keep_enabled = Tools::getValue('keep_info');
			if (null !== $keep_enabled)
			{
				Configuration::updateValue('keep_info', (bool)$keep_enabled);
				$output .= $this->displayConfirmation($this->l('Conservation des information utilisateurs'));
			}
		}
		if (version_compare(_PS_VERSION_, '1.5', '>='))
			$output .= $this->displayForm();
		return $this->display(__FILE__, 'config.tpl').$output;
	}

	public function hookHeader($params)
	{
		$output = $this->ft_omnistart($params);
		if(!isset($_COOKIE["trid"]) && $this->context->customer->isLogged())
		{
			$this->context->controller->addJs($this->_path .'Omnisense.js');
			$output .= $this->ft_user($params);
		}
		$this->context->controller->addJs($this->_path .'Omnisense.js');
		$controller_name = Tools::getValue('controller');
		if(!Context::getContext()->customer->isLogged())
			setcookie('logged','logged' ,time() + 365*24*3600, true);
 		if (($this->context->customer->isLogged() && $controller_name == 'myaccount') ||
			$controller_name == 'identity'|| $controller_name == 'addresses' ||$controller_name == 'orderconfirmation' )
		{
			$output .= $this->ft_user($params);
			setcookie('logged');
			unset($_COOKIE['logged']);
		}
		if ($controller_name == 'orderconfirmation')
			$output .= $this->ft_product_order($params);
		if (Context::getContext()->customer->isLogged() && isset($_COOKIE['logged']))
		{
			$output .= $this->ft_user($params);
			setcookie('logged');
			unset($_COOKIE['logged']);
		}
		if ($controller_name == 'product')
			$output .= $this->ft_product_view($params);

		if(Tools::isSubmit('submitNewsletter'))
			$output .= $this->ft_newsletter($params);
		return $output;
	}

//Bloc Omnisense.start, api_key and identifier
	public function	ft_omnistart($user)
	{
		$key = Configuration::get('key_api');
		$id = Configuration::get('identifier');
		$this->context->smarty->assign("key_api" ,$key);
		$this->context->smarty->assign("id" ,$id);
		if (version_compare(_PS_VERSION_, '1.7', '>='))
			return $this->display(__FILE__, 'omnisenseStart1_7.tpl');
		else
			return $this->display(__FILE__, 'omnisenseStart.tpl');
	}
	public function ft_newsletter($params)
	{
		$email =$_POST['email'];
		if (filter_var($email, FILTER_VALIDATE_EMAIL))
		{
		if($this->context->customer->isLogged() && $email != $this->context->customer->email)
			return;
		$news = array(
				"email" => $email,
				"optin" => "1",
			);
		$this->context->smarty->assign("userInfos", $news);
		return $this->display(__FILE__, 'omnisenseUser.tpl');
		}
		else
			return ;
	}

//Bloc Subscribers
	public function	ft_user($user)
	{
		$address = new Address($this->context->cart->id_address_invoice);
		$gender = NULL;
		if ($this->context->customer->id_gender == 1)
			$gender = "male";
		else if ($this->context->customer->id_gender == 2)
			$gender = "female";
		else
			$gender = "undefined";
		$user1 = array(
				"email" => $this->context->customer->email,
				"first_name" => $this->context->customer->firstname,
				"last_name" => $this->context->customer->lastname,
				"sex" => $gender,
				"phone" => $address->phone_mobile,
				"address" => $address->address1,
				"city" => $address->city,
				"postal_code" => $address->postcode,
				"country" => $address->country,//ISO
				"lang" => $this->context->language->iso_code,
				"company" => $address->company,
				"registered" => true
			);
		if($user1['phone'] == NULL)
			$user1['phone'] = $address->phone;
		$user2 = array (
				"optin" => $this->context->customer->newsletter,
				"birthday" => $this->context->customer->birthday,
		);
		if (Configuration::get('keep_info'))
			$user1 = array_filter($user1);
		$user = array_merge($user1, $user2);
		$this->context->smarty->assign("userInfos", $user);
		return $this->display(__FILE__, 'omnisenseUser.tpl');
	}

//Bloc Order confirmation
	public function	ft_product_order($orderParams)
	{
		$id_order=(int)Tools::getValue('id_order');
		$order = new Order($id_order);
		$currency = new CurrencyCore($order->id_currency);
		$products = $order->getOrderDetailList();
		$nb = 0;
		$output = '';
		foreach ($products as $product)
		{
			$orderParams = array(
				"order_id"=> $order->id,
				"order_amount"=> number_format($order->total_paid_tax_excl, 2),
				"order_currency"=> $currency->iso_code,
				"payment"=> $order->payment,
				"product_id"=> $products[$nb]['product_id'],
				"product_price" => number_format($products[$nb]['product_price'], 2),
				"product_quantity"=>  $products[$nb]['product_quantity']
			);
			$nb++;
			$this->context->smarty->assign("productOrder", $orderParams);
			$output .= $this->display(__FILE__, 'productOrder.tpl');
		}
		return $output;
	}

//Bloc product.view, quick-view < 1.7
	public function	ft_product_view($productParams)
	{
		$id_product = (int)Tools::getValue('id_product');
		$product = $this->context->controller->getProduct();
		$productParams = array(
			"id"=> $id_product,
			"reference"=> $product->reference,
			"name"=> $product->name,
			"category" => $product->category,
			"price"=>number_format($product->price, 2)
		);
		$addCart = array(
			"id"=> $id_product,
			"reference"=> $product->reference,
			"name"=> $product->name,
			"category" => $product->category,
			"price"=>number_format($product->price, 2),
		);
		$this->context->smarty->assign("productView", $productParams);
		$this->context->smarty->assign("productCart", $addCart);
		if (version_compare(_PS_VERSION_, '1.7', '>='))
			return $this->display(__FILE__, 'productView1_7.tpl');
		else
			return  $this->display(__FILE__, 'productView.tpl');
	}

 //Bloc ajax Popup add to cart
	public function hookAjaxCall($params)
	{
			$id_lang = $this->context->language->id;
			$product = new Product($params, false, $id_lang);
		    $addCart = array(
		        "id"=> $params,
		        "reference"=> $product->reference,
		        "name"=> $product->name,
		        "category" => $product->category,
		        "price"=>number_format($product->price, 2)
		    );
			die(json_encode($addCart));
	}
}
?>
