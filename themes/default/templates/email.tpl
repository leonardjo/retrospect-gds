{*
	/** 
	*	Email sub-template
	* $Id$
	*
	*/
*}
<div class="content-title">{$content_title}</div>
{if $sending != true}
<form action="{$PHP_SELF}?m=email" method="post">
<table class="tab-row" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tab-selected">{php}t("Edit"){/php}</td>
		<td class="tab-last">&nbsp;</td>
	</tr>
</table>
<div class="tab-page">
	{if $errors|@count > 0}
	<div class="warning">	
		<p><b>Please correct the following errors:</b></p>
		<p>
			<ul>
				{foreach from=$errors item=error}
					<li><b>{$error}</b></li>
				{/foreach}
			</ul>
		</p>
	</div>
	{/if}
	<!--
	<p>
		{php}t("The following link will be emailed"){/php}:<br /><br/> 
		<a href="{$TRACKBACK_URL}">{$TRACKBACK_URL}</a>
	</p>	
	-->
	<p>{php}t("Your friend's email address"){/php}:<br />
		<input name="to" type="text" class="textbox" size="40" maxlength="40" value="{$to}" />
	</p>
	<p>{php}t("Your email address"){/php}:<br />
		<input name="from" type="text" class="textbox" size="40" maxlength="40" value="{$from}"/>
	</p>
	<p>{php}t("Your full name"){/php}:<br />
	  <input name="name" type="text" class="textbox" size="40" maxlength="40" value="{$name}" />
	</p>
	<p>{php}t("Add a personal message"){/php}:<br />
		<textarea name="message" cols="80" rows="10" class="textbox">{$message}</textarea>
	</p>
	<p>
		<input type="hidden" name="ln" value="{$TRACKBACK_URL}" />
		<input type="submit" name="send" value="Send" />
	</p>
</div>
</form>
{/if}