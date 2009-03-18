<div>
	<div id="orders_response" style="height:100%">
	{script}
	<a class="modalCloseImg simplemodal-close" title="Close"></a>
	  <table>
	  	<tr><td>{navbar}</td></tr>
	  </table>	
	<center>
	  <div id="header">
	    <div id="tabsAndContent">
	      <ul id="tabsNav">
	        <li><a href="#orders">orders</a></li>	      	
	        <li><a href="#method">method</a></li>
	        <li><a href="#type">type</a></li>
	        <li><a href="#discount">discount</a></li>
	      </ul>
	      <ul id="tabContent">
	        <li id="orders">
				{orders}
	        </li>     	      	
	        <li id="method">
				{method}
	        </li>
	        <li id="type">
				{type}
				<div class="suggestionsBox" id="suggestions" style="display: none;">
					<div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
				</div>
	        </li>
	        <li id="discount">
				{discount}
	        </li>   
	      </ul>
	    </div>
	  </div>	  
	</center>
	</div>
</div>