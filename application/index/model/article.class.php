<?php
class article {
	
	public function getinfo($num = 10) {
		return D('article')->limit($num)->select();		
	}

}