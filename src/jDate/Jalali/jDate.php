<?php namespace jDate\Jalali;

/**
 * A LaravelPHP helper class for working w/ jalali dates.
 * by Sallar Kaboli <sallar.kaboli@gmail.com>
 *
 *
 * Based on Laravel-Date bundle
 * by Scott Travis <scott.w.travis@gmail.com>
 * http://github.com/swt83/laravel-date
 *
 *
 * @package     jDate
 * @author      Sallar Kaboli <sallar.kaboli@gmail.com>
 * @link        http://
 * @basedon     http://github.com/swt83/laravel-date
 * @license     MIT License
 */

class jDate
{
	protected $time;

	protected $formats = array(
		'datetime' => '%Y-%m-%d %H:%M:%S',
		'date'     => '%Y-%m-%d',
		'time'     => '%H:%M:%S',
	);

	public static function forge($str = null,$exploadby = '-',$inputtype=null,$mod='')
	{
		$class = __CLASS__;
		return new $class($str,$exploadby,$inputtype,$mod='');
	}

	public function __construct($str = null,$exploadby = '-',$inputtype=null,$mod='')
	{

		if ($str === null){
			$this->time = time();
		}
		else
		{
			if (is_numeric($str)){
				$this->time = $str;
			}elseif($inputtype=='jalali')
			{
				//Added By Mahdi Mirhendi , 0913559139 , Shamsi to Miladi
				$str =explode($exploadby,$str);
				$str= $this->jalali_to_gregorian($str[0] ,$str[1]  ,$str[2],$mod);
				strlen($str[1])==1 ? $str[1]='0'.$str[1] : '';
				strlen($str[2])==1 ? $str[2]='0'.$str[2] : '';
				$str=implode('-',$str);
				$time = strtotime($str);

				if (!$time){
					$this->time = false;
				}
				else{
					$this->time = $time;
				}
			}
			else
			{
				$time = strtotime($str);
				//add shamsi
				if (!$time){
					$this->time = false;
				}
				else{
					$this->time = $time;
				}
			}
		}
	}

	public function time()
	{
		return $this->time;
	}

	public function format($str)
	{
		// convert alias string
		if (in_array($str, array_keys($this->formats))){
			$str = $this->formats[$str];
		}

		// if valid unix timestamp...
		if ($this->time !== false){
			return jDateTime::strftime($str, $this->time);
		}
		else{
			return false;
		}
	}
	public function ago()
	{
		$now = time();
		$time = $this->time();

		// catch error
		if (!$time) return false;

		// build period and length arrays
		$periods = array('ثانیه', 'دقیقه', 'ساعت', 'روز', 'هفته', 'ماه', 'سال', 'قرن');
		$lengths = array(60, 60, 24, 7, 4.35, 12, 10);

		// get difference
		$difference = $now - $time;

		// set descriptor
		if ($difference < 0)
		{
			$difference = abs($difference); // absolute value
			$negative = true;
		}

		// do math
		for($j = 0; $difference >= $lengths[$j] and $j < count($lengths)-1; $j++){
			$difference /= $lengths[$j];
		}

		// round difference
		$difference = intval(round($difference));

		// return
		return number_format($difference).' '.$periods[$j].' '.(isset($negative) ? '' : 'پیش');
	}

	public function until()
	{
		return $this->ago();
	}

	public function jalali_to_gregorian($jy,$jm,$jd,$mod=''){
		list($jy,$jm,$jd)=explode('_',$this->tr_num($jy.'_'.$jm.'_'.$jd));/* <= Extra :اين سطر ، جزء تابع اصلي نيست */
	 if($jy > 979){
	  $gy=1600;
	  $jy-=979;
	 }else{
	  $gy=621;
	 }
	 $days=(365*$jy) +(((int)($jy/33))*8) +((int)((($jy%33)+3)/4)) +78 +$jd +(($jm<7)?($jm-1)*31:(($jm-7)*30)+186);
	 $gy+=400*((int)($days/146097));
	 $days%=146097;
	 if($days > 36524){
	  $gy+=100*((int)(--$days/36524));
	  $days%=36524;
	  if($days >= 365)$days++;
	 }
	 $gy+=4*((int)(($days)/1461));
	 $days%=1461;
	 $gy+=(int)(($days-1)/365);
	 if($days > 365)$days=($days-1)%365;
	 $gd=$days+1;
	 foreach(array(0,31,((($gy%4==0) and ($gy%100!=0)) or ($gy%400==0))?29:28 ,31,30,31,30,31,31,30,31,30,31) as $gm=>$v){
	  if($gd <= $v)break;
	  $gd-=$v;
	 }
	 return($mod==='')?array($gy,$gm,$gd):$gy .$mod .$gm .$mod .$gd;
	}
	function tr_num($str,$mod='en',$mf='٫'){
	 $num_a=array('0','1','2','3','4','5','6','7','8','9','.');
	 $key_a=array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹',$mf);
	 return($mod=='fa')?str_replace($num_a,$key_a,$str):str_replace($key_a,$num_a,$str);
	}
	public function reforge($str)
	{
		if ($this->time !== false)
		{
			// amend the time
			$time = strtotime($str, $this->time);

			// if conversion fails...
			if (!$time){
				// set time as false
				$this->time = false;
			}
			else{
				// accept time value
				$this->time = $time;
			}
		}

		return $this;
	}


}
