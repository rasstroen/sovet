	<?php

// запрещаем вывод ошибок
	error_reporting(1);
	ini_set('display_errors', 1);
	$data = array();
// подключаем файл обработчик
	include_once('dom.php');
	$profile = isset($_GET['profile']) ? $_GET['profile'] : false;
	if(!$profile){
		die('usage:http://test.hardtechno.ru/parser.php?profile=profileName. To view not in json use ?php parameter. by rasstroen http://vkontakte.ru/server_side');
	}
	$html = file_get_html('http://challenge.worldoftanks.ru/uc/accounts/named/' . $profile . '/', false, $context);
// ник бойца
	foreach ($html->find('h1') as $e1) {
		$data['nick'] = $e1->plaintext;
	}
//дата регистрации в игре

	foreach ($html->find('div[class="b-data-create"]') as $e3) {
		$data['regdate'] = $e3->plaintext;
	}
// в каком клане состоит? (вариант 1)
// выводит в одну строку и тэг клана и полное название

	foreach ($html->find('h6 span') as $e41) {
		$data['clan'] = $e41->plaintext;
	}

// в каком клане состоит? (вариант 2)
// выводит в строку тэг клана затем, в следующей строке - полное название клана
// для включения 2 варианта уберите двойные слеши тут и вставьте их выше
// 
// foreach($html->find('h6 span[class="tag"]') as $e41) {
// echo $e41->outertext;
// }
	if (!$data['clan'])
		foreach ($html->find('h6 span[class="name"]') as $e42) {
			$data['clan'] = $e42->outertext;
		}

// должность в клане 

	foreach ($html->find('[class="motto"]') as $e5) {
		$data['clan_role'] = $e5->plaintext;
	}
// Количество дней в клане и Дата вступления в клан

	foreach ($html->find('div.b-statistic td span[class="number"]', 0) as $e61) {
		$data['clan_days'] = $e61->plaintext;
	}

	foreach ($html->find('div.b-statistic td span[class="number"]', 1) as $e62) {
		$data['clan_join_date'] = $e62->plaintext;
	}
// Общие результаты
// Проведено боёв: 

	$i = 0;
	foreach ($html->find('table[width=100%] tr td[width=30%] table.t-table-dotted tr td[class=""]') as $e7) {

		if (!is_numeric($e7->plaintext[1])) {
			$i++;
			$tdata[$i] = $e7->plaintext;
		}
	}
	$i = 0;
	foreach ($html->find('table[width=100%] tr td[width=30%] table.t-table-dotted tr td[class="td-number-nowidth"]') as $e7) {
		if (is_numeric($e7->plaintext[1])) {
			$i++;
			$data['data'][str_replace(':', '', trim($tdata[$i]))] = (int) str_replace('&nbsp;', '', $e7->plaintext);
		}
	}

	$stat = array();
	$stat_tanks = array();
	$k = 0;
	$i = 0;
	foreach ($html->find('table.t-statistic tr') as $e20) {
		if (!$e20->innertext || $k++ < 1)
			continue;
		if ($k < 13) {
			$subhtml = str_get_html($e20->innertext);
			$i++;

			foreach ($subhtml->find('td span') as $e30) {
				$stat[$i]['title'] = trim($e30->plaintext);
			}

			foreach ($subhtml->find('td[class="right value"]') as $e40) {
				if (!isset($stat[$i]['sum']))
					$stat[$i]['sum'] = (int) str_replace('&nbsp;', '', $e40->innertext);
				else
					$stat[$i]['place'] = (int) str_replace('&nbsp;', '', $e40->innertext);
			}
			unset($subhtml);
		}
		else {
			$subhtml = str_get_html($e20->innertext);
			$i++;

			foreach ($subhtml->find('td a') as $e30) {
				$stat_tanks[$i]['title'] = trim($e30->plaintext);
			}

			foreach ($subhtml->find('td[class="right value"]') as $e40) {
				if (!isset($stat_tanks[$i]['games']))
					$stat_tanks[$i]['games'] = (int) str_replace('&nbsp;', '', $e40->innertext);
				else
					$stat_tanks[$i]['wins'] = (int) str_replace('&nbsp;', '', $e40->innertext);
			}
			unset($subhtml);
		}
	}
	$data['tanks'] = array_values($stat_tanks);
	$data['stat'] = array_values($stat);
//дата и время актуализации статистики
// очищаем память
	$html->clear();
	unset($html);

	@ob_end_clean();
	echo isset($_GET['php']) ? '<pre>' . print_r($data, 1) : json_encode($data);
	?>