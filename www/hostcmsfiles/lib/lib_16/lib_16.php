<?php

if (Core::moduleIsActive('poll'))
{
	$Poll_Group_Controller_Show = Core_Page::instance()->object;

	$vote = Core_Array::getPost('vote') && $Poll_Group_Controller_Show->poll;
	if ($vote)
	{
		$oPoll = Core_Entity::factory('Poll', $Poll_Group_Controller_Show->poll);
		$oPoll_Group = $Poll_Group_Controller_Show->getEntity();

		if ($oPoll->poll_group_id == $oPoll_Group->id)
		{
			$aPollResponsesId = array();

			// Множественный
			if ($oPoll->type)
			{
				$aPoll_Responses = $oPoll->Poll_Responses->findAll();
				foreach ($aPoll_Responses as $oPoll_Response)
				{
					$response = Core_Array::getPost('poll_response_' . $oPoll_Response->id);
					if ($response)
					{
						$aPoll_Responses[] = $oPoll_Response->id;
					}
				}
			}
			else
			{
				$aPollResponsesId[] = intval(Core_Array::getPost('poll_response'));
			}

			/* Если был хотя бы один вариант ответа */
			if (count($aPollResponsesId) > 0)
			{
				$voted = Core_Array::get($_COOKIE, 'poll' . $oPoll->id);

				if (!$voted)
				{
					foreach ($aPollResponsesId as $iPollResponseId)
					{
						$oPoll_Response = Core_Entity::factory('Poll_Response', $iPollResponseId);

						$oPoll_Response->voted++;
						$oPoll_Response->save();
					}

					$oPoll->voted++;
					$oPoll->save();

					setcookie("poll" . $oPoll->id, "1", time() + 31536000, '/');
				}

				$Poll_Group_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('ПользовательИмеетПравоОтвечать')->value(intval(!$voted))
				);
			}
		}
	}

	$Poll_Group_Controller_Show->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('ОтображатьСообщениеПользователю')->value(intval($vote))
	)->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('ОтображатьСсылкиНаСледующиеСтраницы')->value(1)
	);

	$xslName = $Poll_Group_Controller_Show->poll
		? Core_Page::instance()->libParams['pollResultXsl']
		: Core_Page::instance()->libParams['pollGroupXsl'];

	$Poll_Group_Controller_Show
		->xsl(
			Core_Entity::factory('Xsl')->getByName($xslName)
		)
		->show();
}
else
{
	?>
	<h1>Опросы</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/polls/">Опросы</a>&raquo; доступен в редакции &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo;.</p>
	<?php
}
?>