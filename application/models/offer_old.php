<?php defined('SYSPATH') or die('No direct script access.');

class Offer_Model extends ORM {

	protected $belongs_to = array('category', 'region', 'type', 'user', 'district', 'subway');
	protected $has_many = array('datas', 'pictures', 'messages', 'complaints', 'bookmarks', 'status_changes');
	protected $has_one = array('offer_notification');
	protected $has_and_belongs_to_many = array('payments');

	protected $objName = 'offer';

	protected $delete_belongings_exception = array('messages');

	protected $property;

	public function __construct($id=NULL)
	{

		$this->sorting['premium_till > "' . date::getForDb() .'"'] = 'desc';
		$this->sorting['positioned'] = 'desc';

		parent::__construct($id);

		if(empty($id)):
			$this->added = date::getForDb();
			$this->positioned = $this->added;
//			$this->premium_till = $this->added;
			$this->expiration = date::getForDb(strtotime('+'.Lib::config('app.'.$this->objName.'_expiration_days').' days'));
		endif;
	}

	public function save() {
		$this->updated = date::getForDb();
		return parent::save();
	}

	public function __get($column){

		switch((string) $column):

			case 'has_user':
				if (!isset($this->object[$column])) {
					$this->object[$column] = !$this->has_not_user;
				}
				return $this->object[$column];
			break;

/**
 * STATUSES
 */
			case 'is_premium':
			case 'premium':
				if (!isset($this->object[$column])) {

					$this->object[$column] = $this->premium_till > date::getForDb();

				}
				return $this->object[$column];
			break;
			case 'is_marked':
			case 'marked':
				if (!isset($this->object[$column])) {

					$this->object[$column] = $this->marked_till > date::getForDb();

				}
				return $this->object[$column];
			break;

			case 'is_positioned':
				if (!isset($this->object[$column])) {

					$this->object[$column] = $this->positioned != $this->added && $this->positioned != $this->premium_set;

				}
				return $this->object[$column];
			break;
			case 'is_banned':
			case 'banned':
				if (!isset($this->object[$column])) {
					$this->object[$column] = ($this->status == 'banned');
				}
				return $this->object[$column];
			break;
			case 'is_user_banned':
				if (!isset($this->object[$column])) {
					$this->object[$column] = ($this->status == 'user_banned');
				}
				return $this->object[$column];
			break;
			case 'is_disabled':
			case 'disabled':
				if (!isset($this->object[$column])) {
					$this->object[$column] = ($this->status == 'disabled');
				}
				return $this->object[$column];
			break;
			case 'is_enabled':
			case 'enabled':
				if (!isset($this->object[$column])) {
					$this->object[$column] = ($this->status === 'enabled' && !$this->is_expired);
				}
				return $this->object[$column];
			break;
			case 'is_bookmarked':
				if (!isset($this->object[$column])) {
					if($user = Auth::instance()->user):
						$this->object[$column] = ORM::factory('bookmark')->where(array($user->foreign_key() => $user->id, $this->foreign_key() => $this->id))->find()->id;
					else:
						$this->object[$column] = false;
					endif;
				}
				return $this->object[$column];
			break;

			case 'is_expired':
				if (!isset($this->object[$column])) {
					$this->object[$column] = $this->expiration < date::getForDb();
				}
				return $this->object[$column];
			break;

			case 'is_viewed_by_owner':
				if (!isset($this->object[$column])) {
/*					if($this->has_not_user):
						$this->object[$column] = false;
					else*/if($user = Auth::instance()->user):
						$this->object[$column] = ($user->id == $this->user_id);
					else:
						$this->object[$column] = false;
					endif;
				}
				return $this->object[$column];
			break;

			case 'is_deleted':
				if (!isset($this->object[$column])) {
					$this->object[$column] = ($this->status == 'deleted');
				}
				return $this->object[$column];
			break;


/**
 * DATA
 */


			case 'fulltitle':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->type->title . ': '. $this->title;
				}
				return $this->object[$column];
			break;

			case 'public_name':
				if (empty($this->object[$column])) {

					if($this->has_not_user):
						if(empty($this->name)):
							$this->object[$column] = '';
						else:
							$this->object[$column] = $this->name;
						endif;
					else:
						$this->object[$column] = $this->user->public_name;
					endif;

				}
				return $this->object[$column];
			break;

			case 'public_name_html':
				if (empty($this->object[$column])) {

					if($this->has_not_user):
						if(empty($this->name)):
							$this->object[$column] = 'Не указано';
						else:
							$this->object[$column] = '<b>'.$this->name.'</b>';
						endif;
					else:
						$this->object[$column] = $this->user->public_name_html;
					endif;

				}
				return $this->object[$column];
			break;
			case 'public_phone':

				if (!isset($this->object[$column])) {
					if(!empty($this->phone)):
						$this->object[$column] = $this->phone;
					elseif($this->has_not_user):
						if(empty($this->phone)):
							$this->object[$column] = '';
						endif;
					else:
						$this->object[$column] = $this->user->public_phone;
					endif;

				}
				return $this->object[$column];

			break;
			case 'public_phone_html':
				if (empty($this->object[$column])) {

					if($this->public_phone):
						$this->object[$column] = '<b>'.format::phone($this->public_phone, NULL, TRUE).'</b>';
					else:
						$this->object[$column] = 'Не указано';
					endif;

				}
				return $this->object[$column];
			break;

			case 'contact_email':
				if (!isset($this->object[$column])) {

					if($this->has_not_user and $this->email):
						$this->object[$column] = $this->email;
					elseif($this->has_user):
						$this->object[$column] = $this->user->contact_email;
					else:
						$this->object[$column] = '';
					endif;

				}
				return $this->object[$column];
			break;

			case 'notification_email':
				if (!isset($this->object[$column])) {

					if($this->has_user):
						$this->object[$column] = $this->user->contact_email;
					elseif(!empty($this->email)):
						$this->object[$column] = $this->email;
					else:
						$this->object[$column] = '';
					endif;

				}
				return $this->object[$column];
			break;


			case 'public_email':
				if (!isset($this->object[$column])) {

					if($this->has_not_user and $this->email and $this->email_status == 'enabled'):
						$this->object[$column] = $this->email;
					elseif($this->has_user):
						$this->object[$column] = $this->user->public_email;
					else:
						$this->object[$column] = '';
					endif;

				}
				return $this->object[$column];
			break;
			case 'public_email_html':
				if (empty($this->object[$column])) {

					if($this->public_email):
						$this->object[$column] = '<b>'.html::mailto($this->public_email).'</b>';
					elseif($this->contact_email):
						$this->object[$column] = '<b><a href="'.$this->url_message.'" class="g modal">Написать&nbsp;сообщение</a></b>';
					else:
						$this->object[$column] = 'Не указано';
					endif;

				}
				return $this->object[$column];
			break;


/**
 * OUTPUT SHORTCUTS
 */

			case 'thumbnail_html':
				if (!isset($this->object[$column])) {
					if($this->pictures->count()):
						$this->object[$column] = '<img src="'.($this->pictures[0]->f('thumb')).'">';
					else:
						$this->object[$column] = '';
					endif;

				}
				return $this->object[$column];
			break;
			case 'act_icons':
				if (!isset($this->object[$column])) {
					$html = '';

					if($this->is_premium or $this->is_viewed_by_owner):
						$html .='<u';

						if($this->is_premium) $html .= ' class="e" title="Премиум до: '.date::getSimple($this->premium_till) .'"';

						$html .= '>';

//						if($this->is_viewed_by_owner) $html .= '<a href="'. $this->url_payment_premium .'" title="Премировать"></a>';
						if($this->is_viewed_by_owner) $html .= '<a class="premium modal" href="'. $this->url_sms_premium .'" title="Премировать"></a>';

						$html .= '</u>';
					endif;

					if($this->is_positioned or $this->is_viewed_by_owner):
						$html .= '<b';
						if($this->is_positioned) $html .= ' class="e" title="Поднято: '.date::getSimple($this->positioned).'"';
						$html .= '>';

//						if($this->is_viewed_by_owner) $html .= '<a href="'.$this->url_payment_position.'" title="Поднять"></a>';
						if($this->is_viewed_by_owner) $html .= '<a class="position modal" href="'.$this->url_sms_position.'" title="Поднять"></a>';

						$html .= '</b>';
					endif;
					if($this->is_marked or $this->is_viewed_by_owner):
						$html .= '<i';

						if($this->is_marked) $html .= ' class="e" title="Выделено до: '.date::getSimple($this->marked_till).'"';

						$html .= '>';

//						if($this->is_viewed_by_owner) $html .= '<a href="'. $this->url_payment_mark .'" title="Выделить"></a>';
						if($this->is_viewed_by_owner) $html .= '<a class="mark modal" href="'. $this->url_sms_mark .'" title="Выделить"></a>';

						$html .= '</i>';
					endif;

					$this->object[$column] = $html;
				}
				return $this->object[$column];
			break;
/* PRICE */
			case 'price_html':

				if (!isset($this->object[$column])) {

					$this->object[$column] = '';

					switch($this->price_type):
						case 'from-to':
							if($this->price):
								$this->object[$column] = 'от ' . (empty($this->price_to) ?  format::money($this->price, $this->currency, false) : format::number($this->price));
							endif;
							if($this->price_to):
								$this->object[$column] .= ' до '. format::money($this->price_to, $this->currency, false);
							endif;
						break;
						case '':
						case 'fixed':
							if ($this->price) $this->object[$column] = format::money($this->price, $this->currency, false);
						break;
						default:
							$this->object[$column] = Lib::config('app.price_type', $this->price_type);
						break;
					endswitch;
				}
				return $this->object[$column];
			break;
			case 'price_html_list':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '';

					switch($this->price_type):
						case 'negotiated':
							$this->object[$column] = '';
						break;
						case 'from-to':
							if($this->price):
								$this->object[$column] = 'от ' . (empty($this->price_to) ?  format::money($this->price, $this->currency, true) : format::number($this->price) . '<br>');
							endif;
							if($this->price_to):
								$this->object[$column] .= 'до '. format::money($this->price_to, $this->currency, true);
							endif;
							$this->object[$column] = '<div class="pri">' . $this->object[$column] .'</div>';
						break;
						default:
							$this->object[$column] = $this->price_html ? '<div class="pri">' . $this->price_html . '</div>' : '';
						break;
					endswitch;
				}
				return $this->object[$column];
			break;
/**
 * URLS
 */
			case 'url_base':
				if (empty($this->object[$column])) {
					$this->object[$column] = '/offer/';
				}
				return $this->object[$column];
			break;
			case 'url':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_base . $this->id .'/';
				}
				return $this->object[$column];
			break;
			case 'url_add':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'add/';
				}
				return $this->object[$column];
			break;
			case 'url_add_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'add_success/';
				}
				return $this->object[$column];
			break;
			case 'url_bookmark':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'bookmark/';
				}
				return $this->object[$column];
			break;
			case 'url_send':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'send/';
				}
				return $this->object[$column];
			break;
			case 'url_send_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'send_success/';
				}
				return $this->object[$column];
			break;
			case 'url_print':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'?print=1';
				}
				return $this->object[$column];
			break;
			case 'url_message':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'message/';
				}
				return $this->object[$column];
			break;
			case 'url_message_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'message_success/';
				}
				return $this->object[$column];
			break;
			case 'url_complaint':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'complaint/';
				}
				return $this->object[$column];
			break;
			case 'url_complaint_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'complaint_success/';
				}
				return $this->object[$column];
			break;
			case 'url_pictures':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'pic/';
				}
				return $this->object[$column];
			break;

/**
 * action URLS
 */
			case 'url_adm':
				if (empty($this->object[$column])) {
					$this->object[$column] = '/adm/offer/' . $this->id .'/';
				}
				return $this->object[$column];
			break;
			case 'url_edit':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'edit/';
				}
				return $this->object[$column];
			break;
			case 'url_edit_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'edit_success/';
				}
				return $this->object[$column];
			break;
			case 'url_remove_complaint':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'remove_complaint/';
				}
				return $this->object[$column];
			break;
			case 'url_ban':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'ban/';
				}
				return $this->object[$column];
			break;
			case 'url_unban':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'unban/';
				}
				return $this->object[$column];
			break;
			case 'url_ban_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'ban_success/';
				}
				return $this->object[$column];
			break;
			case 'url_unban_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'unban_success/';
				}
				return $this->object[$column];
			break;
			case 'url_delete':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'delete/';
				}
				return $this->object[$column];
			break;
			case 'url_undelete':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'undelete/';
				}
				return $this->object[$column];
			break;
			case 'url_delete_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_base.'delete_success/';
				}
				return $this->object[$column];
			break;
			case 'url_remove':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'remove/';
				}
				return $this->object[$column];
			break;
			case 'url_remove_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_base.'delete_success/';
				}
				return $this->object[$column];
			break;

			case 'url_expiration':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'expiration/';
				}
				return $this->object[$column];
			break;
			case 'url_expiration_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'expiration_success/';
				}
				return $this->object[$column];
			break;
			case 'url_disable':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'disable/';
				}
				return $this->object[$column];
			break;
			case 'url_enable':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'enable/';
				}
				return $this->object[$column];
			break;
			case 'url_unpremium':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'unpremium/';
				}
				return $this->object[$column];
			break;
			case 'url_unmark':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'unmark/';
				}
				return $this->object[$column];
			break;
			case 'url_premium':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'premium/';
				}
				return $this->object[$column];
			break;
			case 'url_premium_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'premium_success/';
				}
				return $this->object[$column];
			break;
			case 'url_mark':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'mark/';
				}
				return $this->object[$column];
			break;
			case 'url_mark_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'mark_success/';
				}
				return $this->object[$column];
			break;
			case 'url_position':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'position/';
				}
				return $this->object[$column];
			break;
/**
 * PAYMENT URLS
 */
 			case 'url_payment':
				if (empty($this->object[$column])) {
					$this->object[$column] = '/my/payment/offer/'. $this->id.'/';
				}
				return $this->object[$column];
			break;
			case 'url_payment_premium':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_payment.'premium/';
				}
				return $this->object[$column];
			break;
			case 'url_payment_mark':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_payment.'mark/';
				}
				return $this->object[$column];
			break;
			case 'url_payment_position':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_payment.'position/';
				}
				return $this->object[$column];
			break;
/* SMS */
			case 'url_sms':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'sms/';
				}
				return $this->object[$column];
			break;
			case 'url_sms_premium':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_sms.'premium/';
				}
				return $this->object[$column];
			break;
			case 'url_sms_mark':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_sms.'mark/';
				}
				return $this->object[$column];
			break;
			case 'url_sms_position':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url_sms.'position/';
				}
				return $this->object[$column];
			break;

/**
 * STATUS RELATED
 */

			case 'status_change':
				if (!isset($this->object[$column])) {
					$this->object[$column] = $this->getStatusChange();
				}
				return $this->object[$column];
			break;


		endswitch;

		return parent::__get($column);

	}


	public function getDataByProperty($property_id = NULL){

		if($property_id == NULL) return;

		if(is_array($this->property)) return @$this->property[$property_id];

		if(!$this->datas->count()) return;

		$this->property = array();

		foreach($this->datas as $data):

			if(isset($this->property[$data->property_id])):
				if(!is_array($this->property[$data->property_id])) $this->property[$data->property_id] = array($this->property[$data->property_id]);
				$this->property[$data->property_id][] = $data->datavalue;
			else:
				$this->property[$data->property_id] = $data->datavalue;
			endif;

		endforeach;

		if(!isset($this->property[$property_id])) $this->property[$property_id] = NULL;

		return $this->property[$property_id];
	}


	public function setUserBanned($by = NULL){

		if(!empty($this->id)):

			if($this->status === 'user_banned') return true;

			if($this->status === 'deleted'):

				$status_change = $this->getStatusChange('deleted', $by);

				if($status_change->prev_status === 'banned'):

					$status_change = $this->getStatusChange('banned');

					if($status_change->prev_status != 'user_banned'):
						$this->setStatusChange('user_banned', $status_change->prev_status, $by, 'Заблокирована учётная запись автора.');
						$status_change->prev_status = 'user_banned';
						$status_change->save();
					endif;

				elseif($status_change->prev_status != 'user_banned'):

					$this->setStatusChange('user_banned', $status_change->prev_status, $by, 'Заблокирована учётная запись автора.');
					$status_change->prev_status = 'user_banned';
					$status_change->save();

				else:

					$this->setStatusChange('user_banned', NULL, $by, 'Заблокирована учётная запись автора.');

				endif;

			elseif($this->status === 'banned'):

				$status_change = $this->getStatusChange('banned');

				if($status_change->prev_status != 'user_banned'):

					$this->setStatusChange('user_banned', $status_change->prev_status, $by, 'Заблокирована учётная запись автора.');
					$status_change->prev_status = 'user_banned';
					$status_change->save();

				else:

					$this->setStatusChange('user_banned', NULL, $by, 'Заблокирована учётная запись автора.');

				endif;

			else:

				$this->setStatusChange('user_banned', $this->status, $by, 'Заблокирована учётная запись автора.');
				$this->status = 'user_banned';

				return $this->save();

			endif;

			return true;


		endif;

		return false;

	}

	public function unsetUserBanned($by = NULL){

		if(!empty($this->id)):

			$status_change = $this->getStatusChange('user_banned');

			if($this->status === 'deleted'):

				$deleted_status_change = $this->getStatusChange('deleted');

				if($deleted_status_change->prev_status === 'banned'):

					$banned_status_change = $this->getStatusChange('banned');

					if($banned_status_change->prev_status === 'user_banned'):
						$banned_status_change->prev_status = $status_change->prev_status;
						$banned_status_change->save();
					endif;

				elseif($deleted_status_change->prev_status === 'user_banned'):

					$deleted_status_change->prev_status = $status_change->prev_status;
					$deleted_status_change->save();

				endif;

			elseif($this->status === 'banned'):

				$banned_status_change = $this->getStatusChange('banned');

				if($banned_status_change->prev_status === 'user_banned'):
					$banned_status_change->prev_status = $status_change->prev_status;
					$banned_status_change->save();
				endif;

			elseif($this->status === 'user_banned'):

				$this->status = $status_change->prev_status;
				$changed = true;

			endif;

			if($status_change->id):
				$status_change->delete();
			endif;

			if(!empty($changed)):
				return $this->save();
			else:
				return true;
			endif;

		endif;

		return false;

	}

	public function setBanned($by = NULL, $reason = NULL){

		if(!empty($this->id)):

			if($this->status === 'deleted'):

				$status_change = $this->getStatusChange();

				$this->setStatusChange('banned', $status_change->prev_status, $by, $reason);

				$status_change->prev_status = 'banned';
				$status_change->save();

			elseif($this->status != 'banned'):

				$this->setStatusChange('banned', $this->status, $by, $reason);

				$this->status = 'banned';

				$changed = true;

			elseif($this->status === 'banned'):

				$this->setStatusChange('banned', NULL, $by, $reason);

			endif;

			if($this->checked != 1):
				$this->checked = 1;
				$changed = true;
			endif;

			if(!empty($changed)):
				return $this->save();
			else:
				return true;
			endif;

		endif;

		return false;
	}

	public function unBan(){

		if(!empty($this->id)):

			$status_change = $this->getStatusChange('banned');

			if($this->status === 'banned'):

				$this->status = $status_change->prev_status;
				$changed = true;

			elseif($this->status === 'deleted'):

				$status_change_deleted = $this->getStatusChange();

				if($status_change_deleted->prev_status === 'banned'):

					$status_change_deleted->prev_status = $status_change->prev_status;
					$status_change_deleted->save();

				endif;

			endif;

			if($status_change->id):
				$status_change->delete();
			endif;

			if($this->checked != 1):
				$this->checked = 1;
				$changed = true;
			endif;

			if(!empty($changed)):
				return $this->save();
			else:
				return true;
			endif;

		endif;

		return false;
	}


	public function setDeleted($id = NULL, $by = NULL){

		if(empty($id) and empty($this->id)) return false;

		if(!empty($id)):
			if(is_array($id)):

				$this->in($this->primary_key, $id)->where('status !=', 'deleted');
				$list = $this->find_all();

				$dates = array();

				foreach($list as $item):
					$item->setDeleted(NULL, $by);
				endforeach;

				return true;

			else:

				return ORM::factory($this->object_name, $id)->setDeleted(NULL, $by);

			endif;
		endif;

		if(!empty($this->id)):

			if($this->status === 'deleted'):
				return true;
			endif;

			$this->setStatusChange('deleted', $this->status, $by);

			$this->status = 'deleted';

			return $this->save();

		endif;

	}

	public function unDelete($id = NULL){

		if(empty($id) and empty($this->id)) return false;

		if(!empty($id)):
			if(is_array($id)):

				$this->in($this->primary_key, $id)->where('status', 'deleted');
				$list = $this->find_all();

				$statuses = array();

				foreach($list as $item):
					$statuses[$item->id] = $item->unDelete(NULL, $by);
				endforeach;

				return $statuses;

			else:

				return ORM::factory($this->object_name, $id)->setDeleted(NULL, $by);

			endif;
		endif;


		if(!empty($this->id)):

			$status_change = $this->getStatusChange('deleted');

			if($this->status === 'deleted'):
				$this->status = $status_change->prev_status;
				$changed = true;
			endif;

			if($status_change->id):
				$status_change->delete();
			endif;

			if(!empty($changed)):
				$this->save();
			endif;

			return $this->status;

		endif;

		return false;
	}


	public function setDisabled($id = NULL){
		if(!empty($id)):

			if(is_array($id)):
				$this->db->in($this->primary_key, $id);
			else:
				$this->db->where($this->primary_key, $id);
			endif;

			return $this->db->from($this->table_name)->set('status' , 'disabled')->where('status !=', 'banned')->update();
		endif;

		if(!empty($this->id) and $this->status != 'banned' and $this->status != 'user_banned'):
			$this->status = 'disabled';
			return $this->save();
		endif;

		return false;
	}

	public function setEnabled($id = NULL, $daysToAdd = NULL){

		if(empty($id) and empty($this->id)) return false;

		if(!empty($id)):

			if(is_array($id)):

				$this->in($this->primary_key, $id);
				$list = $this->find_all();

				$dates = array();

				foreach($list as $item):
					$dates[$item->id] = $item->setEnabled(NULL, $daysToAdd);
				endforeach;

				return $dates;

			else:

				return ORM::factory($this->object_name, $id)->setEnabled(NULL, $daysToAdd);

			endif;

		endif;

		if(!empty($this->id)):
			if($this->status != 'enabled'):

				if($this->status != 'banned' and $this->status != 'user_banned' and $this->status != 'deleted'):

					$this->status = 'enabled';

					if($this->expiration < date::getForDb()):
						if(empty($daysToAdd)) $daysToAdd = Lib::config('app.'.$this->objName.'_enable_expiration_days_plus');
						$this->expiration = date::getForDb(strtotime('+'.$daysToAdd.' days'));
					endif;

				endif;

				if($this->save()):
					return $this->expiration;
				endif;

			else:
				return $this->expiration;
			endif;

		endif;

		return false;
	}

	function setChecked() {
		if(!empty($this->id)):
			$this->checked = 1;
			return $this->save();
		endif;
	}

	function unCheck() {
		if(!empty($this->id)):
			$this->checked = 0;
			return $this->save();
		endif;
	}

	public function setExpiration($daysToAdd = NULL, $id = NULL){

		if(empty($id) and empty($this->id)) return false;
		if(!empty($id)) $obj = ORM::factory($this->object_name, $id);
		else $obj = &$this;

		if(empty($daysToAdd)) $daysToAdd = Lib::config('app.'.$this->objName.'_enable_expiration_days_plus');

		if($obj->expiration < date::getForDb()) $obj->expiration = date::getForDb(strtotime('+'.$daysToAdd.' days'));
		else $obj->expiration = date::getForDb(strtotime($obj->expiration . ' +'.$daysToAdd.' days'));

		if($obj->status == 'expired') $obj->status = 'enabled';

		if($obj->save()) return date::getSimple($obj->expiration);
	}


/**
 * PAYMENT SERVICES
 */



	public function setPosition($id = NULL){
		if(empty($id) and empty($this->id)) return false;
		if(!empty($id)) $obj = ORM::factory($this->object_name, $id);
		else $obj = &$this;

		$obj->positioned = date::getForDb();
		if($obj->positioned > $obj->expiration) $obj->expiration = date::getForDb(strtotime('+'.Lib::config('app.'.$this->objName.'_enable_expiration_days_plus').' days'));

		if($obj->status == 'expired') $obj->status = 'enabled';

		if($obj->save()) return date::getLocalizedDateTime($obj->positioned);
	}


	public function setPremium($days_to_add = NULL, $id = NULL){
		if(empty($id) and empty($this->id)) return false;
		if(!empty($id)) $obj = ORM::factory($this->object_name, $id);
		else $obj = &$this;

		if(empty($days_to_add)) $days_to_add = Lib::config('payment.service','premium','amount');

		if($obj->premium_till < date::getForDb()) $obj->premium_till = date::getForDb(strtotime('+'.$days_to_add.' days'));
		else $obj->premium_till = date::getForDb(strtotime($obj->premium_till . ' +'.$days_to_add.' days'));

		if($obj->premium_till > $obj->expiration) $obj->expiration = $obj->premium_till;

		$obj->premium_set = date::getForDb();

		$obj->positioned = date::getForDb();

		if($obj->status == 'expired') $obj->status = 'enabled';

		if($obj->save()) return date::getLocalizedDateTime($obj->premium_till);
	}

	public function unsetPremium($id = NULL){
		if(empty($id) and empty($this->id)) return false;
		if(!empty($id)) $obj = ORM::factory($this->object_name, $id);
		else $obj = &$this;

		if(!empty($obj->premium_till) and $obj->premium_till != '0000-00-00 00:00:00') $obj->premium_till = date::getForDb();

		return $obj->save();
	}

	public function setMarked($days_to_add = NULL, $id = NULL){
		if(empty($id) and empty($this->id)) return false;
		if(!empty($id)) $obj = ORM::factory($this->object_name, $id);
		else $obj = &$this;

		if(empty($days_to_add)) $days_to_add = Lib::config('payment.service','mark','amount');

		if($obj->marked_till < date::getForDb()) $obj->marked_till = date::getForDb(strtotime('+'.$days_to_add.' days'));
		else $obj->marked_till = date::getForDb(strtotime($obj->marked_till . ' +'.$days_to_add.' days'));

		if($obj->marked_till > $obj->expiration) $obj->expiration = $obj->marked_till;

		if($obj->status == 'expired') $obj->status = 'enabled';

		if($obj->save()) return date::getLocalizedDateTime($obj->marked_till);
	}

	public function unsetMarked($id = NULL){
		if(empty($id) and empty($this->id)) return false;
		if(!empty($id)) $obj = ORM::factory($this->object_name, $id);
		else $obj = &$this;

		if(!empty($obj->marked_till) and $obj->marked_till != '0000-00-00 00:00:00') $obj->marked_till = date::getForDb();

		return $obj->save();
	}



/**
 * COUNTERS
 */

	public function addViewsCount(){
		if(empty($this->id)) return false;
		$this->views_count++;
		return $this->save();
	}

	public function count_all_by_user($user_id, $status = NULL, $is_agent = FALSE){

		$where = array(
			'user_id' => $user_id
		);

		if(!$is_agent):
			$where['has_not_user'] = 0;
		endif;
		if($status):
			$where['status'] = $status;
		endif;

		if(!empty($status))	return $this->where($where)->count_all();

		return $this->where(array('user_id' => $user_id))->count_all();
	}

/**
 * Retrieve amount of offers in category
 */

	public function count_all_In_Category($category_id = 0){

		if(!empty($category_id)):
			if(is_array($category_id)):
				$this->in('category_id', $category_id);
			else:
				$this->where('category_id', $category_id);
			endif;
		endif;

		return $this->where('status', 'enabled')->where('expiration >=', date::getForDb())->count_all();

	}

/**
 * Retrieve all offers in category and subs
 */

	public function find_all_In_Category($category_id = 0, $limit = NULL, $offset = 0){

		if(!empty($category_id)):
			if(is_array($category_id)):
				$this->in('category_id', $category_id);
			else:
				$this->where('category_id', $category_id);
			endif;
		endif;

		return $this->where('status', 'enabled')->where('expiration >=', date::getForDb())->find_all($limit, $offset);

	}


	public function setFilters($filters = array()){

		if(!empty($filters['region_id'])):

			$this->where('region_id', (int) $filters['region_id']);

		elseif(AppLib::getUserRegions() and empty($filters['ignore_regions'])):

			$this->in('region_id', AppLib::getUserRegions(true));

		endif;

		if(!empty($filters['category_id'])):
			if(is_array($filters['category_id']) and count($filters['category_id'])):
				$this->in('category_id', $filters['category_id']);
			else:
				$this->where('category_id', $filters['category_id']);
			endif;
		endif;

		if(!empty($filters['type'])):
			$this->where('type_id', (int) $filters['type']);
		endif;

		if(!empty($filters['type_id'])):
			$this->where('type_id', (int) $filters['type_id']);
		endif;

		if(!empty($filters['district_id'])):
			$this->where('district_id', (int) $filters['district_id']);
		endif;

		if(!empty($filters['subway_id'])):
			$this->where('subway_id', (int) $filters['subway_id']);
		endif;

		if((!empty($filters['price_from']) or !empty($filters['price_to'])) and empty($filters['currency'])):
			$currencies = array_keys(Lib::config('payment.currency'));
			$filters['currency'] = $currencies[0];
		endif;

		if(!empty($filters['price_from'])):
			$this->where('price >= ', (int) $filters['price_from']);
			$this->where('currency', $filters['currency']);
		endif;

		if(!empty($filters['price_to'])):
			$this->where('price <= ', (int) $filters['price_to']);
			$this->where('currency', $filters['currency']);
		endif;

		if(!empty($filters['period'])):
			$this->where('positioned >= ', date::getForDb(strtotime('-'.$filters['period'].' days')));
		endif;

		if(!empty($filters['added_period'])):
			$this->where('added >= ', date::getForDb(strtotime('-'.$filters['added_period'].' days')));
		endif;

		if(!empty($filters['phone'])):
			$this->where("phone LIKE '%".format::rawphone($filters['phone'])."%'");
		endif;

		if(!empty($filters['q'])):
            $escaped_query = $this->db->escape($filters['q']);
			$this->where("MATCH(title, description) AGAINST($escaped_query)");
//			$this->orlike(array('title' => $filters['q'], 'description' => $filters['q']));
		endif;

		if(!empty($filters['filter']) and count($filters['filter'])):
			$i = 0;
			foreach($filters['filter'] as $key => $value):
				if($value !== '') $this->join('datas AS d'.$i, array('d'.$i.'.'.$this->foreign_key() => $this->table_name.'.id', 'd'.$i.'.property_id' => '\''.$key.'\'', 'd'.$i.'.datavalue' => '\''.$value.'\''));
				$i++;
			endforeach;

		endif;

		return $this;
	}


	public function setExpiredOffers($user_id = NULL){

		$this->db->from($this->table_name)->set('status','expired')->where(array('expiration <' => date::getForDb(), 'status' => 'enabled'));
		if(!empty($user_id)) $this->db->where('user_id',$user_id);
		return $this->db->update();

	}

	public function removeOldOffers($operation_max_time = NULL){

		$list = ORM::factory($this->object_name)
			//->where('updated <= ', date::getForDb('-'.Lib::config('app.offer_days_to_be_deleted').' days'))
			->where('expiration <=', date::getForDb('-'.Lib::config('app.offer_days_to_be_deleted').' days'))
			->orderby('expiration', 'asc')
			->find_all();

		$startTimer = time();

		if(empty($operation_max_time)) $operation_max_time = Lib::config('app.offer_deletion_operation_max_time');

		$i = 0;
		foreach($list as $offer)
		{
			$offer->delete();
			$i++;
			if((time() - $startTimer) > $operation_max_time) break;
		};
		return $i;
	}

	public function positionOldPremiums(){
		$this->db->from($this->table_name)
			->set('positioned',date::getForDb())
			->set('premium_set',date::getForDb())
			->where('premium_till >', date::getForDb())
			->where('premium_set < ',date::getForDb('-' . Lib::config('app.premium_position_days') . ' days'))
		;

		return $this->db->update();
	}

	public function find_all_owners_offers($limit = NULL, $with_image = TRUE){
		$model = ORM::factory($this->object_name);
		$model->where('user_id', $this->user_id);
		$model->where('has_not_user', 0);
		$model->where('id !=', $this->id);
		return $model->find_all_In_Category(NULL, $limit);
	}

//	используется для простой выборки записей по ID без всяких "умных" сортировок
	public function find_by_ids($ids)
	{
		$sql = 'SELECT *, FIELD(id, \''.implode('\',\'', $ids).'\') AS pos
			FROM '.$this->table_name.'
			WHERE id IN ('.implode(',', $ids).')
			ORDER BY pos
            LIMIT '.count($ids);

		$result = $this->db->query($sql);


		return new ORM_Iterator($this, $result);
	}
}
/* ?> */
