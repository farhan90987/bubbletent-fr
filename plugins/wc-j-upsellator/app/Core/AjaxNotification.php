<?php

namespace WcJUpsellator\Core;

class AjaxNotification
{

    public function throw( $message, $type = 'error')
    {
        wp_send_json( [
                'type' => $type,
                'message' => $message,
                'notification' => true
        ]); 
    }


}