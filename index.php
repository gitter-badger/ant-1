<?php	$time_start = microtime(true);	require 'ant.php';	Ant::init()	->setup(		array(			'view'  => $_SERVER['DOCUMENT_ROOT'] . '/trunk/templates',			'cache' => $_SERVER['DOCUMENT_ROOT'] . '/trunk/cache'		)	)	/*->bind('prepare',function($s){		return '---' . $s . '---';	})	->bind('build',function($s){		return '###' . $s . '###';	})	->bind('exec',function($s){		return '!!!' . $s . '!!!';	})*/; 	/*$s = '	{@import (top) }	<select>	{@forelse ($scope as $k => $s)}		<option value="{{{$k}}}">{{ $s }}</option>	{@empty} 		<option>Array is empty</option>	{@endforelse} 	</select>';	echo Ant::init()	->fromString($s)	->assign(		array(			'scope' => array(				'first',				'second',				'third'			)		)	)	->draw();*/	//echo Ant::init()	//->get('index')	//->draw();	echo Ant::init()	->get('fill')	->draw();		exit(0);	echo Ant::init()	->get('index')	->on('exec',function($s){		return '@@@' . $s . '@@@';	},false)	->assign(		array(			'boom' => 'second',			'title' => 'Hello',			'body' => array(				'first' => 'Awesome',				'second' => 'Bitch',				'fortop' => array(					'ovarahalla' => range(23, 147)				)			),			'escaper' => array(				'nest' => "\"'\'Abelyah'"			),			'inside_suka' => array(				'arr' => array(					array(						'id' => 1,						'name' => 'Jafar'					),					array(						'id' => 2,						'name' => 'Saslan'					)				)			),			'ebel_ehae' => 'Daa',			'mas' => range(1,43),			'obj' => (object)array(				"class" => 'Def'			)		)	)	->draw();	$time_end = microtime(true);	$time = $time_end - $time_start;	echo "<div>{$time}</div>";	echo "<div>" . memory_get_peak_usage (true) / 1024 / 1024 . "</div>";?>