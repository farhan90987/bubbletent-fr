<?php
namespace Custom_Tools;

class Tools_Manager {
    private $tools = array();
    
    public function register_tool($tool_slug, $tool_name, $tool_class) {
        $this->tools[$tool_slug] = array(
            'name' => $tool_name,
            'class' => $tool_class
        );
    }
    
    public function get_tools() {
        return $this->tools;
    }
}