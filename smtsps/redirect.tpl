<p>{$redirect_text}<br /><a href="javascript:history.go(-1);">{$cancel_text}</a></p>
<form action="{$sps_url}" method="post" id="sps_form" class="hidden">
  <input type="hidden" name="affilie" value="{$affiliate}" />
  <input type="hidden" name="Devise" value="{$currency}" />
  <input type="hidden" name="Reference" value="{$reference}" />
  <input type="hidden" name="Montant" value="{$total}" />
  <input type="hidden" name="sid" value="{$session_id}" />
</form>
<script type="text/javascript">
  {literal}
  $(document).ready(function() {
    $('#sps_form').submit();
  });
  {/literal}
</script>
