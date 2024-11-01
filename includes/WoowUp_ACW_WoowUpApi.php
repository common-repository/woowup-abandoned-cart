<?php
class WoowUp_ACW_WoowUpApi{
    private static $url = 'https://api.woowup.com/apiv3';

    public static function existCustomer($customer){
        $customer = json_decode($customer,true);
        $body = [];
        if(isset($customer['email']) && !empty($customer['email'])){
            $body['email'] = $customer['email'];
        }
        if(isset($customer['telephone']) && !empty($customer['telephone'])){
            $body['telephone'] = $customer['telephone'];
        }
        if(empty($body)){
            return null;
        }
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic '. get_option('woowupApikey'),
                'Content-Type'  => 'application/json',
                'Accept' => 'application/json'),
            'body' => $body);
        $response = wp_remote_get(self::$url.'/multiusers/exist',$args);
        if(is_wp_error($response)){
            return null;
        }
        $response = wp_remote_retrieve_body($response);
        $response = json_decode($response);
        return $response->payload->exist ?? null;
    }

    public static function createCustomer($customer){
        if(empty($customer)){
            return null;
        }
        $args = array('headers' => array(
            'Authorization' => 'Basic '. get_option('woowupApikey'),
            'Content-Type'  => 'application/json',
            'Accept' => 'application/json'),
            'body' => $customer);
        $response = wp_remote_post(self::$url.'/users',$args);
        if(is_wp_error($response)){
            return null;
        }
        $code = wp_remote_retrieve_response_code($response);
        return $code==201;
    }

    public static function createAbandonedCart($cart){
        if(empty($cart)){
            return null;
        }
        $abandonedCart = [];
        $customer = json_decode($cart->customer,true);
        $abandonedCart['email'] = $customer['email'];
        $abandonedCart['total_price'] = (float)$cart->total_price;
        $abandonedCart['external_id'] = $cart->external_id;
        $abandonedCart['source'] = 'web';
        $abandonedCart['recover_url'] = $cart->recover_url;
        //$abandonedCart['createtime'] = $cart->createtime;
        $abandonedCart['products'] = [];
        $products = json_decode($cart->products,true);
        foreach($products as $product){
            $abandonedCart['products'][] =[
                'sku' => $product['sku'],
                'quantity' => (int)$product['quantity'],
                'unit_price' => (float)$product['unit_price']
            ];
        }
        $abandonedCart = json_encode($abandonedCart);
        $args = array('headers' => array(
            'Authorization' => 'Basic '. get_option('woowupApikey'),
            'Content-Type'  => 'application/json',
            'Accept' => 'application/json'),
            'body' => $abandonedCart);
        $response = wp_remote_post(self::$url.'/multiusers/abandoned-cart',$args);
        if(is_wp_error($response)){
            return null;
        }
        $code = wp_remote_retrieve_response_code($response);
        return $code == 201;
    }
}