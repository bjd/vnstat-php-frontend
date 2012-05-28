/**
 * @returns {LiveMonitor}
 */
function LiveMonitor(iface, startText)
{
    this._iface = iface;
    
    this._refreshHandler = null;
    this._refreshInterval = 5000;

    this._idPrefix = 'live';

    if (LiveMonitor.initialized == undefined)
    {
        LiveMonitor.prototype.doLive = function(action)
        {
            var obj = this;
            
            if(action == undefined)
                action = '';

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
                    if(data.indexOf(startText) == -1 && obj._refreshHandler == null)
                        obj.start();
                    else 
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
            clearInterval(this._refreshHandler);
            this._refreshHandler = null;
            
            this.doLive('stop');
        }
    }
    LiveMonitor.initialized = true;
}