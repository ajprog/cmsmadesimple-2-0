{validation_errors for=$page_object}

<div class="pagecontainer">

  <div class="title">
    {$header_name}
  </div>

  {if $error_msg}
  <div class="error">
    <p>{$error_msg}</p>
  </div>
  {/if}

  {if $message}
  <div class="message">
    <p>{$message}</p>
  </div>
  {/if}

	<form method="post" name="contentform" enctype="multipart/form-data" id="contentform" action="{$action}">

		<div id="page_tabs">
			<ul>
				<li><a href="#content"><span>Content</span></a></li>
				<li><a href="#advanced"><span>Advanced</span></a></li>
				<li><a href="#permissions"><span>Permissions</span></a></li>
				{if $can_preview eq true}
				  <li><a href="#preview"{if $showpreview eq true} class="active"{/if} onclick="xajax_ajaxpreview(xajax.getFormValues('contentform'));return false;"><span>Preview</span></a></li>
				{/if}
			</ul>

			<div id="content">
				{html_hidden id='serialized_content' name='serialized_content' value=$serialized_object}
				{html_hidden id='orig_page_type' name='orig_page_type' value=$orig_page_type}
				{html_hidden id='orig_current_language' name='orig_current_language' value=$orig_current_language}

				{* Page Type *}
				{admin_input type='select' label='contenttype' id='page_type' name='page_type' options=$page_types selected=$selected_page_type onchange='document.contentform.submit();'}
				
				{* Language *}
				{admin_input type='select' label='Language' id='current_language' name='current_language' options=$languages selected=$orig_current_language}

				{* Page Title *}
				{if $page_object->field_used('name')}				
					{admin_input type='input' label='title' id='content_name' name='name' value=$name useentities='true'}					  
				{/if}

				{* Menu Text *}
				{if $page_object->field_used('menu_text')}
					{admin_input type='input' label='menutext' id='content_menu_text' name='menu_text' value=$menu_text useentities='true'}					  
				{/if}

				<div id="page_content_blocks">
					{section name=onefile loop=$include_templates}
						{include file=$include_templates[onefile]}
					{/section}
				</div>
   
				{* Parent Dropdown *}
				{if $show_parent_dropdown eq true}

				
				  <div class="row">
					<label>{lang string='parent'}:</label>
				  	  {$parent_dropdown}
				  </div>
				{/if}
			</div> <!-- End content -->

			<div id="advanced">
		    {* Template Dropdown *}
		    {if $page_object->field_used('template_id')}
		      <div class="row">
		      	<label>{lang string='template'}:</label>
		      	  {$template_names}
		      </div>
		    {/if}

		    {* Active Checkbox *}
		    {if $page_object->field_used('active')}
				{admin_input type='checkbox' label='active' id='content_active' name='content[active]'  selected=$page_object->active}					  
			{/if}
    
		    {* Show in Menu Checkbox *}
		    {if $page_object->field_used('show_in_menu')}
				{admin_input type='checkbox' label='showinmenu' id='content_show_in_menu' name='content[show_in_menu]'  selected=$page_object->show_in_menu}					  
		    {/if}
    
		    {* Cacheable Flag *}
		    {if $page_object->field_used('cachable')}
				{admin_input type='checkbox' label='cachable' id='content_cachable' name='content[cachable]'  selected=$page_object->cachable}					  			
		    {/if}
    
		    {* Owner Dropdown *}
		    {if $show_owner_dropdown eq true}
		      <div class="row">
		      	<label>{lang string='owner'}:</label>
		      	  {$owner_dropdown}
		      </div>
		    {/if}
    
		    {* Metadata *}
		    {if $page_object->field_used('metadata')}
		      <div class="row">
		        <label>{lang string='metadata'}:</label>
		          {$metadata_box}
		      </div>
		    {/if}
    
		    {* Ttile Attribute *}
		    {if $page_object->field_used('title_attribute')}
				{admin_input type='input' label='titleattribute' id='content_title_attribute' name='content[title_attribute]'  value=$page_object->title_attribute}					  			
		    {/if}

		    {* Tab Index *}
		    {if $page_object->field_used('tab_index')}
				{admin_input type='input' label='tabindex' id='content_tab_index' name='content[tab_index]'  value=$page_object->tab_index}
		    {/if}
    
		    {* Access Key *}
		    {if $page_object->field_used('access_key')}
				{admin_input type='input' label='accesskey' id='content_access_key' name='content[access_key]'  value=$page_object->access_key}			
		    {/if}
    
		    {* Page Alias *}
		    {if $page_object->field_used('alias')}
				{admin_input type='input' label='pagealias' id='content_alias' name='content[alias]'  value=$page_object->alias useentities='true'}						
		    {/if}
		    <div class="clearb"></div>

			</div> <!-- End advanced -->
			
			<div id="permissions">
				
				{section name='defns' loop=$permission_defns}

					<h4>{$permission_defns[defns].name}</h4>
					
					<table cellpadding="5" cellspacing="0" border="0">
						<thead>
							<tr>
								<th>Group</th>
								<th>Allow</th>
								<th>Inherited From</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							{foreach item='entry' from=$permission_defns[defns].entries}
								<tr style="color: #666;" class="{cycle values="row1,row2"}">
									<td>{$entry.group_name}</td>
									<td>{$entry.has_access}</td>
									<td>{$entry.object_name}</td>
									{if $entry.object_id eq $page_object.id and $entry.object_id gt -1}
										<td><a href="blah.php">Edit</a></td>
										<td><a href="blah.php">Delete</a></td>
									{else}
										<td></td>
										<td></td>
									{/if}
								</tr>
							{/foreach}
						</tbody>
					</table>
					
					<hr />

				{/section}
				
				<br />
				

				{admin_input type='select' label='Group' id='group_permission' name='content[group]' options='Everyone'}						
				{admin_input type='select' label='Permission' id='permission_list' name='content[permission_list]' options=$permission_list}										
				{admin_input type='checkbox' label='Allow' id='Allow' name='content[Allow]'}										
				{admin_input type='submit' value='Submit' id='submit_button'}
					
			</div> <!-- End permissions -->

			{if $can_preview eq true}
			<div id="preview">
			  <iframe name="previewframe" class="preview" id="previewframe" src="{$previewfname}"></iframe>
			</div> <!-- End preview -->
			{/if}

		</div> <!-- End tabs -->


		<div class="footerbuttons buttons">

{* generic buttons template *}
{include file='buttons.tpl'}

{* todo: remove these *}
<!--		    <input type="submit" name="submitbutton" value="{lang string='submit'}" class="pagebutton" onmouseover="this.className='pagebuttonhover'" onmouseout="this.className='pagebutton'" />

		    <input type="submit" name="cancel" value="{lang string='cancel'}" class="pagebutton" onmouseover="this.className='pagebuttonhover'" onmouseout="this.className='pagebutton'" />
		    {if $can_apply eq true}
		      <input type="submit" name="applybutton" value="{lang string='apply'}" class="pagebutton" onmouseover="this.className='pagebuttonhover'"   onmouseout="this.className='pagebutton'" />
		    {/if}
		    {if $can_preview eq true}
		      <input type="submit" name="previewbutton" value="{lang string='preview'}" class="pagebutton" onmouseover="this.className='pagebuttonhover'" onmouseout="this.className='pagebutton'" onclick="xajax_ajaxpreview(xajax.getFormValues('contentform'));return false;" />
-->
		    {/if}
		</div> <!-- end footerbuttons -->

  </form>

</div> <!-- End Page Container -->

<script type="text/javascript">
<!--
	$('#page_tabs').tabs({$start_tab});
//-->
</script>