/**
 * @returns {LiveMonitor}
 */
function LiveMonitor(iface)
{
    this._iface = iface;
    
    this._refreshHandler = null;
    this._refreshInterval = 5000;
    
    this._startText = 'Start Live';
    this._idPrefix = 'live';

    if (LiveMonitor.initialized == undefined)
    {
        LiveMonitor.prototype.doLive = function(action)
        {
            var obj = this;

            jQuery.ajax({
                url  : 'live.php',
                type : 'POST',
                data :
                {
                    action : action,
                    'if'   : this._iface
                },
                success : function(data)
                {
                    if(action != 'stop')
                        jQuery('#' + obj._idPrefix + obj._iface).html(data);
                }
            });
        }

        LiveMonitor.prototype.start = function()
        {
            this.doLive('start');
            
            var obj = this;

            this._refreshHandler = setInterval(function() { obj.doLive('start'); }, this._refreshInterval);
        }

        LiveMonitor.prototype.stop = function()
        {
            this.doLive('stop');
            
            var htmlStartText = "<a href=\"javascript:" + this._iface + ".start()\">" + this._startText + "</a>";

            jQuery('#' + this._idPrefix + this._iface).html(htmlStartText);

            clearInterval(this._refreshHandler);
            this._refreshHandler = null;
        }
    }
    LiveMonitor.initialized = true;
}