<?php
namespace Common\Html {
	class TagCloud {
		protected $_terms;
		protected $_order;
		protected $_min;
		const ORDER_ALPHABETIC = 0;
		const ORDER_NONE = 1;
		const ORDER_RANDOM = 2;

		public function __construct(array $terms, $order = self::ORDER_ALPHABETIC)
		{
			$this->_terms = $terms;
			$this->_order = $order;
			$min = $max = current($terms);
			if(is_array($min))
				$min = $max = $min['count'];
			foreach ($terms as $term => $count) {
				if(is_array($count))
					$count = $count['count'];
				$min = $count < $min ? $count : $min;
				$max = $count > $max ? $count : $max;
			}
			$this->_min = $min;
			$this->_max = $max;
		}

		public function __toString()
		{
			$ret = array();
			foreach ($this->_terms as $term => $count) {
				$round = $count > 0 && $this->_max > $this->_min ? round(($count - $this->_min) / (($this->_max - $this->_min) / 10)) : 5;
				$ret[] = '<div class="tagCloud tagCloud-' . $round . '">' . $term . '</div>';
			}
			switch ($this->_order) {
				case self::ORDER_ALPHABETIC:
					ksort($this->_terms);
					break;
				case self::ORDER_RANDOM:
					shuffle($ret);
					break;
			}
			return implode('', $ret);
		}

		public function withInfo()
		{
			$ret = array();
			foreach ($this->_terms as $term => $info) {
				$tmp = '<div class="tagCloud';
				if(isset($info['colour']))
					$tmp.=' tagCloud-' .$info['colour'];
				if(isset($info['count'])){
					$count = (int)$info['count'];
					
					$round = $count > 0 && $this->_max > $this->_min ? round(($count - $this->_min) / (($this->_max - $this->_min) / 10)) : 5;
					$tmp.=' tagCloud-' .$round;
				}
				$tmp.='">' . $term . '</div>';
				$ret[] = $tmp;
			}
			switch ($this->_order) {
				case self::ORDER_ALPHABETIC:
					ksort($this->_terms);
					break;
				case self::ORDER_RANDOM:
					shuffle($ret);
					break;
			}
			return implode('', $ret);
		}
	}
}
