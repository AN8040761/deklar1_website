<?php

// Please don't modify or delete the copyright notice. Doing that is a violation of GPL.

print<<<EOF
<style type="text/css">
div#scm-usernotes {
	background-color: transparent;
}
div#scm-usernotes div.scm-head, div#scm-usernotes div.scm-foot {
	background-color: transparent;
	padding: 4px;
}
div#scm-usernotes div.scm-foot {
	text-align: center;
}
div#scm-usernotes div.scm-foot a, div#scm-usernotes div.scm-head a {
	background-color: transparent;
}
div#scm-usernotes span.action {
	float: right;
}
div#scm-usernotes div.scm-note {
	margin-left: 2em; 
	margin-right: 2em; 
	border-bottom:1px dashed;
	padding: 4px;
}
div#scm-usernotes div.scm-text {
	padding: 2px;
	margin-top: 4px;
}
div.scm-head, div.scm-text{
        text-align: center;
}
</style>

<div id="scm-usernotes">
 <div class="scm-head">
 <H3>{$COM_LANG['header']}</H3>
 </div>
EOF;

 if ($comments_count) {
  for($i=0; $i<$comments_count; $i++) {
   if ($dont_show_email[$i] != '1' && $email != '') { $author[$i] = "<a href=\"mailto:{$email[$i]}\">{$author[$i]}</a>"; }
   $text[$i] = str_replace(chr(13), '<br />', $text[$i]);
   
   print<<<EOF
 <div class="scm-note">
  <strong>{$author[$i]}</strong><br />
  <small>{$time[$i]}</small>
  <div class="scm-text">
  {$text[$i]}
  </div>
 </div>
EOF;
  
  }
 }
 else {
   print<<<EOF
 <div class="note">
  <div class="scm-text">
  {$COM_LANG['no_comments_yet']}
  </div>
 </div>
EOF;
 }
?> 
 <div class="scm-foot">
     <form method=POST action='<?=$COM_CONF['script_url'] ?>'>
	              <input type=hidden name="action" value="add">
                  <input type=hidden name="href" value="<?=$_SERVER['REQUEST_URI'] ?>">
		  <table width="290" border="0" cellspacing="1" cellpadding="2" align="center">
		    <tr> 
            <td width="83" align="right"><font color="red">*</font><?=$COM_LANG['Name'] ?>: 
		        </td>
		      <td width="196" align="left">
		        <input type=text name="disc_name" maxlength=40 size=30>
                <input type=hidden name="r_disc_name" value="<?=$COM_LANG['r_disc_name'] ?>">
		        </td>
		    </tr>
		    <tr> 
            <td width="83" align="right"><?=$COM_LANG['E-mail'] ?>:</font></td>
		      <td width="196" align="left">
		        <input type="Text" name="disc_email" size="30" maxlength="70">
		        </td>
		    </tr>
		    <tr> 
		      <td width="83"></td>
		      <td width="196" align="left">
              <input type="checkbox" name="email_me"><font size=2><nobr><?=$COM_LANG['Notify'] ?></nobr></font><br>
              <input type="checkbox" name="dont_show_email" CHECKED><font size=2><nobr><?=$COM_LANG['Dont_show_email'] ?></nobr></font><br>
		        </td>
		    </tr>
		    <tr> 
		      <td valign="top" width="83" align="right"> 
              <font color="red">*</font><?=$COM_LANG['Text'] ?>:
		      </td>
		      <td valign="top" width="196" align="left">
		        <textarea name="disc_body" cols="40" rows="13" wrap="VIRTUAL"></textarea>
                <input type=hidden name="r_disc_body" value="<?=$COM_LANG['r_disc_text'] ?>">
		        </td>
            </tr>
            <? if ($COM_CONF['check_for_spam_method'] == 'recaptcha') { ?>
            <tr>
            <td>&nbsp;</td>
            <td>
            <?
                require_once($COM_CONF['full_path'] . '/recaptcha/recaptchalib.php');
                echo recaptcha_get_html($COM_CONF['recaptcha_publickey']);
            ?>
            </td>
            </tr>
            <? } ?>
		    <tr> 
		      <td valign="top" width="83" align="right">&nbsp; </td>
		      <td valign="top" width="196">
		        <div align="center">
                <input type="submit" name="Submit" value="<?=$COM_LANG['Submit'] ?>">
		          </div>
		      </td>
		    </tr>
		  </table>
		</form>
		<!-- ScritsMill Comments Copyright Notice -->
          Powered by  <a href="http://www.scriptsmill.com/comments.html">Scriptsmill Comments Script</a>
		<!-- /ScritsMill Comments Copyright Notice -->
 </div>
</div>

		