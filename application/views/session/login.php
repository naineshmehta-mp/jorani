<?php 
/*
 * This file is part of Jorani.
 *
 * Jorani is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jorani is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jorani.  If not, see <http://www.gnu.org/licenses/>.
 */

$CI =& get_instance();
$CI->load->library('polyglot');
$CI->load->helper('language');
$this->lang->load('session', $language);
$this->lang->load('global', $language);
$this->lang->load('menu', $language);?>

<style>
    body {
        background-image:url('<?php echo base_url();?>assets/images/login-background.jpg');
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }
    
    .vertical-center {
        min-height: 90%;  /* Fallback for browsers do NOT support vh unit */
        min-height: 90vh;
        display: flex;
        align-items: center;
      }
      
      .form-box {
        padding: 20px;
        border: 1px #e4e4e4 solid;
        border-radius: 4px;
        box-shadow: 0 0 6px #ccc;
        background-color: #fff;
      }
</style>

    <div class="row vertical-center">
        <div class="span3">&nbsp;</div>
            <div class="span6 form-box">
                <div class="row-fluid">
                    <div class="span6">
<h2><?php echo lang('session_login_title');?><?php echo $help;?></h2>

<?php if($this->session->flashdata('msg')){ ?>
<div class="alert fade in" id="flashbox">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <?php echo $this->session->flashdata('msg'); ?>
</div>
<script type="text/javascript">
//Flash message
$(document).ready(function() {
    $(".alert").alert();
});
</script>
<?php } ?>

<?php echo validation_errors(); ?>

<?php
$attributes = array('id' => 'loginFrom');
echo form_open('session/login', $attributes);
$languages = $CI->polyglot->nativelanguages($this->config->item('languages'));?>

    <input type="hidden" name="last_page" value="session/login" />
    <?php if (count($languages) == 1) { ?>
    <input type="hidden" name="language" value="<?php echo $language_code; ?>" />
    <?php } else { ?>
    <label for="language"><?php echo lang('session_login_field_language');?></label>
    <select class="input-medium" name="language" id="language" onchange="Javascript:change_language();">
        <?php foreach ($languages as $lang_code => $lang_name) { ?>
        <option value="<?php echo $lang_code; ?>" <?php if ($language_code == $lang_code) echo 'selected'; ?>><?php echo $lang_name; ?></option>
        <?php }?>
    </select>
    <?php } ?>
    <label for="login"><?php echo lang('session_login_field_login');?></label>
    <input type="text" class="input-medium" name="login" id="login" value="<?php echo set_value('login'); ?>" autofocus required />
    <input type="hidden" name="CipheredValue" id="CipheredValue" />
</form>
    <input type="hidden" name="salt" id="salt" value="<?php echo $salt; ?>" />
    <label for="password"><?php echo lang('session_login_field_password');?></label>
    <input class="input-medium" type="password" name="password" id="password" /><br />
    <br />
    <button id="send" class="btn btn-primary"><i class="icon-user icon-white"></i>&nbsp;<?php echo lang('session_login_button_login');?></button><br />
    <br />
    <?php if ($this->config->item('ldap_enabled') == FALSE) { ?>
    <button id="cmdForgetPassword" class="btn btn-info"><i class="icon-envelope icon-white"></i>&nbsp;<?php echo lang('session_login_button_forget_password');?></button>
    <?php } ?>
    <textarea id="pubkey" style="visibility:hidden;"><?php echo $public_key; ?></textarea>
                </div>
                <div class="span6" style="height:100%;">
                    <div class="row-fluid">
                        <div class="span12">
                            <img src="<?php echo base_url();?>assets/images/logo_simple.png">
                        </div>
                    </div>
                    <div class="row-fluid"><div class="span12">&nbsp;</div></div>
                    <div class="row-fluid">
                        <div class="span12">
                            <span style="font-size: 250%; font-weight: bold; line-height: 100%;"><center><?php echo lang('menu_banner_slogan');?></center></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="span3">&nbsp;</div>
    </div>

<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.pers-brow.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jsencrypt.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/bootbox.min.js"></script>
<script type="text/javascript">
    //Refresh page language
    function change_language() {
        $.cookie('language', $('#language option:selected').val(), { expires: 90, path: '/'});
        $('#loginFrom').prop('action', '<?php echo base_url();?>session/language');
        $('#loginFrom').submit();
    }
    
    $(function () {
        //Memorize the last selected language with a cookie
        if($.cookie('language') != null) {
            var IsLangAvailable = 0 != $('#language option[value=' + $.cookie('language') + ']').length;
            if ($.cookie('language') != "<?php echo $language_code; ?>") {
                //Test if the former selected language is into the list of available languages
                if (IsLangAvailable) {
                    $('#language option[value="' + $.cookie('language') + '"]').attr('selected', 'selected');
                    $('#loginFrom').prop('action', '<?php echo base_url();?>session/language');
                    $('#loginFrom').submit();
                }
            }
        }
        
        $('#send').click(function() {
            var encrypt = new JSEncrypt();
            encrypt.setPublicKey($('#pubkey').val());
            //Encrypt the concatenation of the password and the salt
            var encrypted = encrypt.encrypt($('#password').val() + $('#salt').val());
            $('#CipheredValue').val(encrypted);
            $('#loginFrom').submit();
        });
        
        //If the user has forgotten his password, send an e-mail
        $('#cmdForgetPassword').click(function() {
            if ($('#login').val() == "") {
                bootbox.alert("<?php echo lang('session_login_msg_empty_login');?>");
            } else {
                $.ajax({
                   type: "POST",
                   url: "<?php echo base_url(); ?>session/forgetpassword",
                   data: { login: $('#login').val() }
                 })
                 .done(function(msg) {
                   switch(msg) {
                       case "OK":
                           bootbox.alert("<?php echo lang('session_login_msg_password_sent');?>");
                           break;
                       case "UNKNOWN":
                           bootbox.alert("<?php echo lang('session_login_flash_bad_credentials');?>");
                           break;
                   }
                 });
            }
        });
        
        //Validate the form if the user press enter key in password field
        $('#password').keypress(function(e){
            if(e.keyCode==13)
            $('#send').click();
        });
    });
</script>
