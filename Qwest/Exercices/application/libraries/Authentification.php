<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authentification{
    public function permission($niveau)
    {
        $CI = &get_instance();
        
        if($CI->session->id !== null){
            $sql = 'SELECT permission FROM user WHERE id = ?';
            $reponse = $CI->db->query($sql, $CI->session->id)->result()[0]->permission;
            
//            var_dump($reponse);
//            var_dump($niveau);
//            die;
            if($reponse === $niveau or $niveau === "C"){
                $sql = 'SELECT count(id) as compte FROM message WHERE destinataire_id = ? AND lu = ?';
                $nbMessages = intval($CI->db->query($sql, array(
                    $CI->session->id,
                    0
                ))->result()[0]->compte);
                
                if($nbMessages === 0)
                {
                    $texteMessage = "Messagerie";
                }

                elseif($nbMessages === 1)
                {
                    $texteMessage = "<span class=\"nouveau-message\">Un nouveau message</span>";
                }

                elseif($nbMessages > 1)
                {
                    $texteMessage = "<span class=\"nouveau-message\">" . strval($nbMessages) . " nouveaux messages</span>";
                }
                
                $CI->session->set_userdata("texteMessage", $texteMessage);
                
//                var_dump($_SESSION["pseudo"]);
//                die;
                return true;
            }
            
        }
        
        show_404();
    }
}