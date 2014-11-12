function taxToggle(details,switcher,expandedClassName)
{if($(details).style.display=='none'){$(details).show();$(switcher).addClassName(expandedClassName);}else{$(details).hide();$(switcher).removeClassName(expandedClassName);}};var CentinelAuthenticate=Class.create();CentinelAuthenticate.prototype={initialize:function(blockId,iframeId)
{this._isAuthenticationStarted=false;this._relatedBlocks=new Array();this.centinelBlockId=blockId;this.iframeId=iframeId;if(this._isCentinelBlocksLoaded()){$(this.centinelBlockId).hide();}},isAuthenticationStarted:function()
{return this._isAuthenticationStarted;},addRelatedBlock:function(blockId)
{this._relatedBlocks[this._relatedBlocks.size()]=blockId;},_hideRelatedBlocks:function()
{for(var i=0;i<this._relatedBlocks.size();i++){$(this._relatedBlocks[i]).hide();}},_showRelatedBlocks:function()
{for(var i=0;i<this._relatedBlocks.size();i++){$(this._relatedBlocks[i]).show();}},_isRelatedBlocksLoaded:function()
{for(var i=0;i<this._relatedBlocks.size();i++){if(!$(this._relatedBlocks[i])){return false;}}
return true;},_isCentinelBlocksLoaded:function()
{if(!$(this.centinelBlockId)||!$(this.iframeId)){return false;}
return true;},start:function(authenticateUrl)
{if(this._isRelatedBlocksLoaded()&&this._isCentinelBlocksLoaded()){this._hideRelatedBlocks();$(this.iframeId).src=authenticateUrl;$(this.centinelBlockId).show();this._isAuthenticationStarted=true;}},success:function()
{if(this._isRelatedBlocksLoaded()&&this._isCentinelBlocksLoaded()){this._showRelatedBlocks();$(this.centinelBlockId).hide();this._isAuthenticationStarted=false;}},cancel:function()
{if(this._isAuthenticationStarted){if(this._isRelatedBlocksLoaded()){this._showRelatedBlocks();}
if(this._isCentinelBlocksLoaded()){$(this.centinelBlockId).hide();$(this.iframeId).src='';}
this._isAuthenticationStarted=false;}}};;