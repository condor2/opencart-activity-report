<?php
class ControllerExtensionModuleUserActivity extends Controller {
	private $error = array(); 
	
	public function index() {
		$this->load->language('extension/module/user_activity');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/user_activity', 'user_token=' . $this->session->data['user_token'], true),
   		);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/user_activity', $data));

	}
	
	public function install() {
		$query = $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "information_to_category (category_id INT(11) , information_id INT(11), PRIMARY KEY (category_id))");
        
		$query = $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "user_activity` (
                `user_activity_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `key` varchar(64) NOT NULL,
                `data` text NOT NULL,
                `ip` varchar(40) NOT NULL,
                `date_added` datetime NOT NULL,
               PRIMARY KEY (`user_activity_id`)
            ) DEFAULT COLLATE=utf8_general_ci;");

		$this->load->model('setting/event');

		$this->model_setting_event->addEvent('UserActivityAddUser', 'admin/model/user/user/addUser/after', 'user/user_activity/addActivityAddUser');
		$this->model_setting_event->addEvent('UserActivityEditUser', 'admin/model/user/user/editUser/after', 'user/user_activity/addActivityEditUser');
		$this->model_setting_event->addEvent('UserActivityDeleteUser', 'admin/model/user/user/deleteUser/after', 'user/user_activity/addActivityDeleteUser');
		$this->model_setting_event->addEvent('UserActivityForgottenUser', 'admin/model/user/user/editCode/after', 'user/user_activity/addActivityForgottenUser');
		$this->model_setting_event->addEvent('UserActivityResetUser', 'admin/model/user/user/editPassword/after', 'user/user_activity/addActivityResetUser');
		$this->model_setting_event->addEvent('UserActivityLoginUser', 'admin/controller/common/login/after', 'user/user_activity/addActivityLoginUser');

		$this->model_setting_event->addEvent('UserActivityAddProduct', 'admin/model/catalog/product/addProduct/after', 'user/user_activity/addActivityAddProduct');
		$this->model_setting_event->addEvent('UserActivityAddCategory', 'admin/model/catalog/category/addCategory/after', 'user/user_activity/addActivityAddCategory');
		$this->model_setting_event->addEvent('UserActivityAddStore', 'admin/model/setting/store/addStore/after', 'user/user_activity/addActivityAddStore');

		$this->model_setting_event->addEvent('UserActivityEditProduct', 'admin/model/catalog/product/editProduct/after', 'user/user_activity/addActivityEditProduct');
		$this->model_setting_event->addEvent('UserActivityEditCategory', 'admin/model/catalog/category/editCategory/after', 'user/user_activity/addActivityEditCategory');
		$this->model_setting_event->addEvent('UserActivityEditStore', 'admin/model/setting/store/editStore/after', 'user/user_activity/addActivityEditStore');

		$this->model_setting_event->addEvent('UserActivityDeleteProduct', 'admin/model/catalog/product/deleteProduct/after', 'user/user_activity/addActivityDeleteProduct');
		$this->model_setting_event->addEvent('UserActivityDeleteCategory', 'admin/model/catalog/category/deleteCategory/after', 'user/user_activity/addActivityDeleteCategory');
		$this->model_setting_event->addEvent('UserActivityDeleteStore', 'admin/model/setting/store/deleteStore/after', 'user/user_activity/addActivityDeleteStore');

		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'user/user_activity');
		$this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'user/user_activity');
	}
	
	public function uninstall() {
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEvent('user_activity_login', 'admin/controller/common/login/index/after', 'model/user/user_activity/addActivity');

		$this->model_setting_event->deleteEvent('user_activity', 'admin/controller/common/login/index/after', 'admin/controller/user/user_activity/addActivity');
		
		$this->model_setting_event->deleteEvent('user_activity', 'admin/model/catalog/product/editProduct/after', 'admin/controller/user/user_activity/addActivity');
	}
	 
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/user_activity')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
