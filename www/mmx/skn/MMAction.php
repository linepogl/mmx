<?php

abstract class MMAction extends Action {
//	public function GetDefaultMode(){ return Action::MODE_HTML_FRAGMENT; }
	public function IsPermitted(){ return true; }

	public final function Render(){
		echo Js::BEGIN;
		if ($this->mode === Action::MODE_NORMAL) {
			echo "jQuery(document).ready(function(){";
			echo "window.mmx.loading_page = true;";
			$this->RenderJavascriptInitialState();
			$this->RenderJavascript();
			echo "window.mmx.loading_page = false;";
			echo "});";
		}
		else {
			echo "window.mmx.ClearMain();";
			$this->RenderJavascript();
		}
		echo Js::END;
	}
	public function RenderJavascriptInitialState() {
		echo "window.mmx.OpenTab(".new Js($this->GetName()).",".new Js($this->GetHrefPlain()).",true);";
	}
	public abstract function RenderJavascript();

}