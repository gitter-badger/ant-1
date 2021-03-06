<?php
	namespace Ant;
	
	class Parser
	{
		private static $skips = array();

		public static function parse($view, $path = null)
		{
			$rules = Ant::getRule();
			foreach ($rules as $rx => $call) {
				$view = preg_replace_callback($rx, $call, $view);
			}

			$view = Inherit::extend($view, $path);
			$view = preg_replace_callback('/@skip.+?@endskip/ms', '\Ant\Parser::skip', $view);
			$view = preg_replace_callback('/@php.+?@endphp/ms', '\Ant\Parser::skip', $view);
			$view = preg_replace_callback('/{{--.*?--}}/ms', '\Ant\Parser::comment', $view);
			$view = preg_replace_callback('/{{{.+?}}}/ms', '\Ant\Parser::variable', $view);
			$view = preg_replace_callback('/{{.+?}}/ms', '\Ant\Parser::escape', $view);
			$view = preg_replace_callback('/@import.+/', '\Ant\Parser::import', $view);
			$view = preg_replace_callback('/[\s\t]+@(case|default)/', '\Ant\Parser::caseSpace', $view);
			$view = preg_replace_callback('/\B@(forelse|foreach|for|while|switch|case|default|if|elseif|else|unless|each)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', '\Ant\Parser::control', $view);
			$view = preg_replace_callback('/\B@(empty|break|continue|endforeach|endforelse|endfor|endwhile|endswitch|endif|endunless)/', '\Ant\Parser::endControl', $view);

			if (self::$skips) {
				$view = str_replace(
					array_keys(self::$skips),
					array_values(self::$skips),
					$view
				);

				self::$skips = array();
			}
			
			$view = str_replace(
				array('@php', '@endphp', '@skip', '@endskip'),
				array('<?php', '?>', '', ''),
				$view
			);

			return $view;
		}

		public static function skip($e)
		{
			$uniqid = '~SKIP_' . strtoupper(str_replace('.', '', uniqid('',true))) . '_CONTENT~';
			self::$skips[$uniqid] = $e[0];

			return $uniqid;
		}

		public static function comment($e)
		{
			return '';
		}

		public static function each($view, $collection, $item = 'item', array $scope = array())
		{
			if(Fn::iterable($collection)) {
				$tmpl = $tmpl = Ant::init()->fromFile($view);

				foreach ($collection as $single) {
					$scope[$item] = $single;

					echo $tmpl->assign($scope)->draw();
				}
			}
		}

		public static function import($e)
		{
			$view = trim(str_replace('@import', '', $e[0]));
			$view = substr($view, 1, -1);
			
			$args = explode(',', $view);
			if (1 == count($args)) {
				$args[] = null;
			}

			list($tmpl, $assign) = $args;

			$tmpl   = Helper::findVariable($tmpl);
			$assign = Helper::findVariable($assign); 

			return '<?php echo \Ant\Ant::init()->get(' . $tmpl .')->assign(' . $assign . ')->draw(); ?>';
		}

		public static function variable($e)
		{
			$view = trim(str_replace(array('{{{','}}}'), '', $e[0]));
			
			$view = Helper::findVariable($view);
			$view = Helper::findOr($view);
			
			return '<?php echo ' . $view . '; ?>';
		}

		public static function escape($e)
		{
			$view = trim(str_replace(array('{{', '}}'), '', $e[0]));

			$view = Helper::findVariable($view);
			$view = Helper::findOr($view);

			return '<?php echo \Ant\Fn::escape(' . $view . '); ?>';
		}

		public static function caseSpace($e)
		{
			return ltrim($e[0]);
		}

		public static function control($e)
		{
			$op = trim($e[1]);

			if ($op == 'each') {
				$view = 'Ant\Parser::each' . Helper::findVariable($e[3]);
			} else if ($op == 'unless') {
				$view = 'if(!' . Helper::findVariable($e[3]) . ')'; 
			} else if ($op == 'forelse') {
				$m = array();
				preg_match('/(\$|->)[A-Za-z0-9_\.]+/', $e[4], $m);
				$parsed = Helper::parseVariable($m[0]);

				$view = 'if(\Ant\Fn::iterable(' . $parsed . ') and count(' . $parsed .  ')): foreach' . Helper::findVariable($e[3]);
			} else {
				$view = $op . Helper::findVariable($e[3]);
			}

			if ('each' != $op and ':' != substr($view,-1)) {
				$view .= ':';
			}

			return '<?php ' . $view . ' ?>';
		}

		public static function endControl($e)
		{
			$view = trim($e[1]);

			if ($view == 'endforelse' or $view == 'endunless') {
				$view = 'endif';
			} else if($view == 'empty') {
				$view = 'endforeach; else:';
			}

			return '<?php ' . $view . '; ?>';
		}
	}
?>