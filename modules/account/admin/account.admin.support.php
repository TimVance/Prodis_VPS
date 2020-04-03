<?php
/**
 * Редактирование модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
    $path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

/**
 * Account_admin_support
 */
class Account_admin_support extends Frame_admin
{
  /**
	 * @var object вспомогательный объект модуля
	 */
	private $account = null;

  /**
	 * @var array массив текущих ошибок
	 */
	public $self_errors = array();

  /**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
    Custom::inc('modules/account/admin/account.admin.inc.php');
		$this->account = new Account_admin_inc($this->diafan);
	}

  /**
	 * Выводит содержание "Персональная страница"
   *
	 * @return void
	 */
	public function show()
	{
    if(! $this->account->is_auth())
    {
      $this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/'.$this->diafan->_admin->module.'/');
    }
    if(! $this->diafan->_route->show)
		{
      $this->info_block();
      $this->warn();
      $this->subjects();
		}
		else
		{
      if($this->diafan->_route->page)
      {
        $this->diafan->redirect($this->diafan->_route->current_admin_link(array('page')));
      }
      $this->warn();
			$this->questions();
		}
	}

  /**
	 * Выводит содержание "Информационный блок"
   *
	 * @return void
	 */
	public function info_block()
	{
    if(! $this->account->is_auth())
    {
      return;
    }
    $url = $this->account->uri('support', 'info');
    if(! $support = $this->diafan->_client->request($url, $this->account->token))
    {
      return;
    }
    $this->diafan->_client->get_attributes($support, 'warning', 'alarm');

    echo '
    <div class="box box_height">';
    echo $support["warning"];
    echo '
    </div>';

    if(! empty($support["alarm"]))
    {
      echo '
      <div class="box box_height">';
      echo $support["alarm"];
      echo '
      </div>';
    }
	}

  /**
	 * Выводит содержание сообщения "Предупреждение"
   *
	 * @return void
	 */
	public function warn()
	{
    if(! $this->account->is_auth())
    {
      return;
    }
    $url = $this->account->uri('support', 'warn');
    if(! $result = $this->diafan->_client->request($url, $this->account->token))
    {
      return;
    }
    $this->diafan->_client->get_attributes($result, 'content', 'checked');

    if(! $result["content"])
    {
      return;
    }

    if($result["checked"])
    {
      return;
    }

    echo '
    <div class="box box_height" id="warn_box">';
    echo $result["content"];
    echo '
    <form method="POST" action="'.$this->diafan->_route->current_admin_link().'" enctype="multipart/form-data">
      <input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
      <input type="hidden" name="action" value="warn_read">
			<input type="hidden" name="module" value="'.$this->diafan->_admin->module.'">';
    Custom::inc('adm/includes/edit.php');
    $object = new Edit_admin($this->diafan);
    $key = "warn_read"; $name = $this->diafan->_("Ознакомлен");
		$value = !! $result["checked"];
		$help = "Если отмечено, то Вы поддтверждаете, что информация Вами прочитана.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$object->show_table_tr_checkbox($key, $name, $value, $help, $disabled, $attr, $class);
    unset($object);
    echo '
      </form>';
    echo '
    </div>';
	}

  /**
	 * Выводит содержание "Список тем"
   *
	 * @return void
	 */
	public function subjects()
	{
    if(! $this->account->is_auth())
    {
      return;
    }
    $url = $this->account->uri('support', 'subjects').($this->diafan->_route->page ? 'page'.$this->diafan->_route->page.'/' : '');
    if(! $result = $this->diafan->_client->request($url, $this->account->token))
    {
      if(! empty($this->diafan->_client->errors) && ! empty($this->diafan->_client->errors['no_support']))
      {
        echo '
        <div class="box box_height">';
        echo '<br><div class="attention">'.$this->diafan->_client->errors['no_support'].'</div>';
        echo '
        </div>';
      }
      return;
    }
    $this->diafan->_client->get_attributes($result, 'rows_new', 'rows');

    echo '
    <div class="box box_height">';

    if (! empty($result["rows_new"]))
    {
    	echo '<h2>'.$this->diafan->_('Активные запросы').'</h2>';
    	foreach($result["rows_new"] as $row)
    	{
        $this->diafan->_client->get_attributes($row, 'link', 'date', 'name', 'theme', 'answer', 'my', 'autor', 'avatar', 'id');

        echo '
    		<div class="subject">';
        // переопределяем ссылку
        $row["link"] = BASE_PATH.ADMIN_FOLDER.'/'.$this->diafan->_admin->rewrite.'/'.'show'.$row["id"].'/';
    		echo '
          <a class="date" href="'.$row["link"].'">'.($row["date"] ? $this->diafan->format_date($row["date"], false, false, 5) : '&nbsp;').'</a>
          <a class="title" href="'.$row["link"].'">'.$row["name"].($row["theme"] == 5 ? ' <sup style="color: #18c139;" title="'.$this->diafan->_('Техническое задание на доработку').'">'.$this->diafan->_('ТЗ').'</sup>' : '').'</a>';
    		if($row["answer"])
    		{
    			if($row["autor"] == "DIAFAN.CMS")
          {
            echo '<span class="unreaded" style="background-color: #488bb9;">'.$this->diafan->_('Есть ответ службы поддержки').'</span>';
          }
          else
          {
            echo '<span class="unreaded">'.$this->diafan->_('На рассмотрении').'</span>';
          }
    		}
    		echo '
        </div>';
    	}
    }


    $this->form();


    if(! empty($result["rows"]))
    {
    	echo '<h2>'.$this->diafan->_('Архив запросов').'</h2>';
      foreach($result["rows"] as $row)
    	{
        $this->diafan->_client->get_attributes($row, 'link', 'date', 'name', 'theme', 'answer', 'my', 'autor', 'avatar');

        // переопределяем ссылку
        $row["link"] = BASE_PATH.ADMIN_FOLDER.'/'.$this->diafan->_admin->rewrite.'/'.'show'.$row["id"].'/';
    		echo '
    		<div class="subject">
    			<a class="date" href="'.$row["link"].'">'.($row["date"] ? $this->diafan->format_date($row["date"], false, false, 5) : '&nbsp;').'</a>
    			<a class="title" href="'.$row["link"].'">'.$row["name"].($row["theme"] == 5 ? ' <sup style="color: #18c139;" title="'.$this->diafan->_('Техническое задание на доработку').'">'.$this->diafan->_('ТЗ').'</sup>' : '').'</a>
    			<a class="message" href="'.$row["link"].'">';
    		if($row["answer"])
    		{
    			echo '
            <span class="src">'.($row["my"] ? $this->diafan->_('ВЫ') : $this->diafan->_('DIAFAN.CMS')).':</span> '.$this->diafan->short_text($row["answer"], 50).'';
    		}
    		echo '
          </a>
        </div>';
    	}
    }

    $paginator = '';
    $links = $this->prepare_paginator(false);
    $paginator .= '<div class="paginator">';
    $paginator .= $this->diafan->_tpl->get('get_admin', 'paginator', $links);
    $paginator .= '</div>';
    echo $paginator;

    echo '
    </div>';
	}

  /**
	 * Выводит содержание "Список сообщений в теме"
   *
	 * @return void
	 */
	public function questions()
	{
    if(! $this->account->is_auth())
    {
      return;
    }
    $url = $this->account->uri('support', 'questions').($this->diafan->_route->show ? 'show'.$this->diafan->_route->show.'/' : '').($this->diafan->_route->page ? 'page'.$this->diafan->_route->page.'/' : '');
    if(! $result = $this->diafan->_client->request($url, $this->account->token))
    {
      if(! empty($this->diafan->_client->errors) && ! empty($this->diafan->_client->errors['no_support']))
      {
        echo '
        <div class="box box_height">';
        echo '<br><div class="attention">'.$this->diafan->_client->errors['no_support'].'</div>';
        echo '
        </div>';
        return;
      }
      $this->diafan->redirect($this->diafan->_route->current_admin_link(array('page', 'show')));
      return;
    }
    $this->diafan->_client->get_attributes($result, 'subject', 'theme', 'date', 'rows', 'avatar', 'form');


    echo '
    <div class="box box_height">';

    echo '
    <div class="questions">
      <span class="title">'.$result["subject"].($result["theme"] == 5 ? ' <sup style="color: #18c139;" title="'.$this->diafan->_('Техническое задание на доработку').'">'.$this->diafan->_('ТЗ').'</sup>' : '').'</span>
      <span class="date">'.($result["date"] ? $this->diafan->format_date($result["date"], false, false, 5) : '&nbsp;').'</span>
    </div>';


    echo '
    <div class="block messages_container scrollable">
    	<div class="messages">';

    foreach($result["rows"] as $i => $row)
    {
      $this->diafan->_client->get_attributes($row, 'anons', 'created', 'files', 'id', 'text', 'act', 'new', 'avatar', 'date_answer', 'attachs', 'passed', 'passed_time', 'user_answer');
    	if($row["anons"])
    	{
    		echo '
    		<div class="message_item">
    			<div class="avatar"><img src="'.$result["avatar"].'" /></div>
    			<div class="message_body">
    				<h3 class="you">'.$this->diafan->_('ВЫ').'<span class="date">'.$this->diafan->format_date($row["created"], false, false, 5).'</span></h3>
    				'.$row["anons"];
    				if($row["files"])
    				{
    					echo '<div class="attaches">';
    					foreach($row["files"] as $f)
    					{
    						if(! empty($f["img"]))
    						{
    							echo '<a class="attachment" href="'.$f["link"].'" rel="prettyPhoto[support'.$row["id"].']"><img src="'.$f["img"].'" alt="" height="'.$f["height"].'" width="'.$f["width"].'"/></a>';
    						}
    						else
    						{
    							echo '<a class="attachment" href="'.$f["link"].'">'.$f["name"].'</a>';
    						}
    					}
    					echo '</div>';
    				}
    				echo '
    			</div>
    		</div>';
    	}
    	if($row["text"] && $row["act"])
    	{
    		echo '
    		<div class="message_item';
    		if(! empty($row["new"]))
    		{
    			echo ' active';
    		}
    		echo '">
    			<a class="avatar"><img src="'.$row["avatar"].'" width="42"/></a>
    			<div class="message_body">
    				<h3>'.$this->diafan->_('DIAFAN.CMS').':<span class="date">'.$this->diafan->format_date($row["date_answer"], false, false, 5).'</span></h3>
    				'.$row["text"];
    				if($row["attachs"])
    				{
    					echo '<div class="attaches">';
    					foreach($row["attachs"] as $f)
    					{
    						if(! empty($f["img"]))
    						{
    							echo '<a class="attachment" href="'.$f["link"].'" rel="prettyPhoto[user'.$row["id"].']"><img src="'.$f["img"].'" alt="" height="'.$f["height"].'" width="'.$f["width"].'" /></a>';
    						}
    						else
    						{
    							echo '<a class="attachment" href="'.$f["link"].'">'.$f["name"].'</a>';
    						}
    					}
    					echo '</div>';
    				}
    				echo '
    			</div>
    		</div>';
    	}
    	if(! $row["act"] && $row["passed"] && $i == count($result["rows"]) - 1)
    	{
    		echo '
    		<div class="message_item">
    			<a class="avatar"><img src="'.$row["avatar"].'" width="42"/></a>
    			<div class="message_body">
    				<h3>'.$this->diafan->_('DIAFAN.CMS').':';
    				if($row["passed_time"])
    				{
    					echo '<span class="date">'.$this->diafan->format_date($row["passed_time"], false, false, 5).'</span>';
    				}
    				echo '</h3>';
    				if($row["user_answer"] == 3)
    				{
    					echo $this->diafan->_('Ваш запрос переведен ведущему разработчику. Решение может занять несколько дней. Ожидайте, пожалуйста.');
    				}
    				else
    				{
    					echo $this->diafan->_('Ваш запрос передан другому специалисту, компетентному в этом вопросе. Ожидайте ответа.');
    				}
    			echo '
    			</div>
    		</div>';
    	}
    }
    echo '
    	</div>
    </div>';

    echo '
    </div>';


    echo '
    <div class="box box_height">';
    $this->form($result["form"]);
    echo '
    </div>';
	}

  /**
   * Формирует форму запроса
   *
   * @param array $form массив значений для формы вопроса
   * @return void
   */
  private function form($form = false)
  {
    echo '<a name="support"></a>';
    $add = (! empty($_POST["form_action"]) && $_POST["form_action"] == 'add');
    if($add)
    {
      $this->diafan->_client->get_attributes($_POST,
        "message", "theme", "subject", "domain",
        "subject_id", "created");
      $param = array(
        "message"    => $_POST["message"],
        "theme"      => $_POST["theme"],
        "subject"    => $_POST["subject"],
        "domain"     => $_POST["domain"],

        "subject_id" => $_POST["subject_id"],
        "created"    => $_POST["created"],
      );
      $files = array();
      if(! empty($_FILES['attachments']))
      {
        $attachments = $_FILES['attachments'];
        foreach ($attachments['tmp_name'] as $n => $filename)
  			{
          if(empty($filename)) continue;
          $tmp = $this->account->dir_path.'/'.md5('support'.$this->diafan->uid());
					File::upload_file($filename, $tmp);
          $param['@'.'attachments'.'['.$n.']'] = array("tmp_name" => '@'.ABSOLUTE_PATH.$tmp);
          if(! empty($attachments['type'][$n])) $param['@'.'attachments'.'['.$n.']']["type"] = $attachments['type'][$n];
          if(! empty($attachments['name'][$n])) $param['@'.'attachments'.'['.$n.']']["name"] = $attachments['name'][$n];
          $files[] = $tmp;
  			}
      }
      $url = $this->account->uri('support', (! $this->diafan->_route->show ? 'add_subject' : 'add_question'));
      $result = $this->diafan->_client->request($url, $this->account->token, $param);
      foreach ($files as $filename) unlink($filename);
      if($result)
      {
        $this->diafan->one_shot(array(
          "module" => $this->diafan->_admin->module,
          "rewrite" => $this->diafan->_admin->rewrite,
          "method" => 'add'
        ), true);
        $this->diafan->redirect($this->diafan->_route->current_admin_link());
      }
      $this->self_errors = $this->diafan->_client->errors;
    }
    if($this->diafan->one_shot(array(
      "module" => $this->diafan->_admin->module,
      "rewrite" => $this->diafan->_admin->rewrite,
      "method" => 'add')))
    {
      echo '
        <br><br>
        <div class="request_support">
          <div class="ok">'.$this->diafan->_('Обращение в службу поддержки DIAFAN.CMS отправлено.').'</div>
        </div>';
    }
    elseif($add && $this->self_errors)
    {
      echo '
        <br><br>
        <div class="request_support">
          <div class="error">'.$this->diafan->_('Обращение в службу поддержки DIAFAN.CMS не отправлено.').'</div>
        </div>';
    }
    $url = $this->account->uri('support', 'form').($this->diafan->_route->show ? 'show'.$this->diafan->_route->show.'/' : '');
    if($result = $this->diafan->_client->request($url, $this->account->token))
    {
      $this->diafan->_client->get_attributes($result, 'form', 'attention', 'extensions', 'theme');
      if (! empty($result["form"]) && is_array($result["form"]))
      {
        if($form && is_array($form))
        {
          $form = array_merge($form, $result["form"]);
        }
        else $form = $result["form"];
        $this->diafan->_client->get_attributes($form, 'domains', 'created', 'subject_id', 'no_act');

        if( $this->diafan->_route->show && ($form["created"]+86400*7) < time() )
    		{
    			echo '<div class="attention">'.$this->diafan->_('Внимание! Текущий запрос старше недели, поэтому оперативный ответ не гарантируется. Лучше %sсоздать новый запрос%s.', '<a href="'.$this->diafan->_route->current_admin_link(array('show', 'page')).'#support'.'">', '</a>').'</div>';
    		}

        $extensions = $result["extensions"];
        echo '
        <form method="POST" action="'.$this->diafan->_route->current_admin_link().'#support'.'" enctype="multipart/form-data" class="form_support ajax">
          <input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
          <input type="hidden" name="form_action" value="add">';
        if(! $form["subject_id"])
        {
          echo '
          <h2>'.$this->diafan->_('Создать новый запрос в службу поддержки').'</h2>';
        }
        else
        {
          echo '
          <input type="hidden" name="subject_id" value="'.$form["subject_id"].'">
          <input type="hidden" name="created" value="'.$form["created"].'">';
        }
        if(! empty($result["attention"]))
        {
          echo '<div class="attention_box">'.$result["attention"].'</div>';
        }
        if(! $form["subject_id"] && $result["theme"] && is_array($result["theme"]))
        {
          echo '
          <div class="unit" id="theme">
            <div class="infofield">'.$this->diafan->_('Тип вопроса').'</div>
            <select name="theme">';
          foreach($result["theme"] as $key => $value)
          {
            echo '
              <option value="'.$key.'"'.(! empty($_POST["theme"]) && $_POST["theme"] == $key ? ' selected="selected"' : '').'>'.$value.'</option>
            ';
          }
          echo '
            </select>
          </div>';
        }
        if(! $form["subject_id"])
        {
          echo '
          <div class="unit" id="subject">
            <div class="infofield">'.$this->diafan->_('Тема вопроса').'</div>
            <input type="text" name="subject" placeholder="'.$this->diafan->_('Определите тему вопроса').'"'.(! empty($_POST["subject"]) ? ' value="'.$_POST["subject"].'"' : '').'>
            '.(isset($this->self_errors["subject"])
              ? '<div class="error">'.$this->self_errors["subject"].'</div>'
              : '').'
          </div>';
          echo '
          <div class="unit" id="domains">';
          // echo '
          //   <div class="infofield">'.$this->diafan->_('Вопрос по сайту').'</div>
          //   <input type="text" name="domain" placeholder="'.$this->diafan->_('Укажите доменное имя сайта').'"'.(! empty($_POST["domains"]) ? ' value="'.$_POST["domains"].'"' : ' value="'.$this->diafan->domain(true).'"').' id="domain">';
          // if($form["domains"])
          // {
          //   echo '
          //   <span class="form-tags">';
          //   foreach($form["domains"] as $i => $d)
          //   {
          //     if($i == 2)
          //     {
          //       echo '
          //     <a class="js-moretags">'.$this->diafan->_('ещё').' '.(count($form["domains"]) - 2).'</a><span class="tagspane">';
          //     }
          //   echo '
          //     <a class="tag" data-target="#domain">'.mb_strtolower($d).'</a> ';
          //   }
          //   if($i > 1) echo '</span>';
          //   echo '
          //   </span>';
          // }
          echo '
            <input type="hidden" name="domain"'.(! empty($_POST["domains"]) ? ' value="'.$_POST["domains"].'"' : ' value="'.$this->diafan->domain(true).'"').'>';
          echo (isset($this->self_errors["domain"])
          ? '<div class="error">'.$this->self_errors["domain"].'</div>'
          : '');
          echo '
          </div>';
        }

        $form['bbcode'] = array(
  				'tag' => 'support',
  				"value" => (! empty($_POST["message"]) ? $_POST["message"] : ''),
  				"name" => "message",
  				"placeholder" => "Сообщение",
  			);
        echo '
          <div class="unit" id="message">
            <div class="infofield">'.$this->diafan->_('Содержание вопроса').'</div>'
            .$this->diafan->_tpl->get('get', 'bbcode', $form["bbcode"]);
            $this->diafan->_admin->js_view[] = 'modules/bbcode/js/bbcode.get.js';
        echo (isset($this->self_errors["message"])
          ? '<div class="error">'.$this->self_errors["message"].'</div>'
          : '');
        echo '
          </div>';

        echo '
          <div class="unit" id="attachments">
            <div class="infofield">'.$this->diafan->_('Прикрепляемый файл').':</div>
            <input type="file" class="file multiple" name="attachments[]">
            <div>'.$this->diafan->_('Типы файлов: %s. Иные форматы прикладывайте заархивированные в .zip', $extensions).'</div>
            '.(isset($this->self_errors["attachments"])
              ? '<div class="error">'.$this->self_errors["attachments"].'</div>'
              : '').'
          </div>';

        if($form["no_act"])
        {
        	echo '
          <div class="unit" id="subject_close">
            <input class="button subject_close" type="button" value="Закрыть запрос" data-subject_id="'.$form["subject_id"].'">
          </div>';
        }

        echo '
          <div class="unit" id="message_submit">
            <input class="button" type="submit" value="Отправить">
            '.(isset($this->self_errors["other_error"])
              ? '<div class="error">'.$this->self_errors["other_error"].'</div>'
              : '').'
          </div>
        </form>';
      }
    }
  }

  /**
	 * Формирует постраничную навигацию
	 *
   * @param integer $id родитель
	 * @return integer
	 */
	private function prepare_paginator($id)
	{
		if (! $id || ! $this->diafan->variable_list('plus'))
		{
			$this->diafan->_paginator->navlink = ( $this->diafan->_admin->rewrite ? $this->diafan->_admin->rewrite.'/' : '' ).( $this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '' ).( $this->diafan->_route->cat ? 'cat'.$this->diafan->_route->cat.'/' : '' );
			// $enterlink = $this->diafan->_paginator->navlink.'parent%d/'.( $this->diafan->_paginator->page ? 'page'.$this->diafan->_paginator->page.'/' : '' ).'?';
			$this->diafan->_paginator->get_nav = $this->diafan->get_nav;
			// $navlink .= $this->diafan->_paginator->navlink.'parent%d/'.( $this->diafan->_paginator->page ? 'page'.$this->diafan->_paginator->page.'/' : '' ).( $this->diafan->get_nav ? $this->diafan->get_nav.'&' : '?' );
		}
		elseif ($this->diafan->variable_list('plus'))
		{
			$this->diafan->_paginator->page = ! empty($_GET["page".$id]) ? intval($_GET["page".$id]) : 0;
			$this->diafan->_paginator->urlpage = '?page'.$id.'=%d';
			$navlink = ( $this->diafan->_admin->rewrite ? $this->diafan->_admin->rewrite.'/' : '' ).( $this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '' ).( $this->diafan->_route->cat ? 'cat'.$this->diafan->_route->cat.'/' : '' ). 'parent%d/';
			$this->diafan->_paginator->navlink = sprintf($navlink, $id);
		}

		$this->diafan->_paginator->nen = $this->diafan->_client->paginator->nen;

    $admin_nastr = $this->diafan->_users->admin_nastr;
    $this->diafan->_users->admin_nastr = $this->diafan->_client->paginator->nastr;

		$links = $this->diafan->_paginator->get();

    $this->diafan->_users->admin_nastr = $admin_nastr;

		return $links;
	}
}
