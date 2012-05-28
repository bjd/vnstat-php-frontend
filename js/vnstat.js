/**
 * @returns {LiveMonitor}
 */
function LiveMonitor(iface, stopText)
{
    this._idPrefix = 'live';
    this._refreshInterval = 5000;
    this._refreshHandler  = null;

    this._iface = iface;
    this._stopText = stopText;

    if (LiveMonitor.initialized == undefined)
    {
        LiveMonitor.prototype.monitor = function(action)
        {
            if(action == undefined)
                action = '';

            var obj = this;

            jQuery.ajax({
                url  : 'live.php',
                type : 'POST',
                data :
                {
                    action : action,
                    if     : this._iface
                },
                success : function(data)
                {
                    // restarts Live Monitoring when data contains locale Stop Live text
                    // and refreshHandler is not configured to poll live datas
                    if(data.indexOf(obj._stopText) != -1 && obj._refreshHandler == null)
                        obj.start();
                    else 
                        jQuery('#' + obj._idPrefix + obj._iface).html(data);
                }
            });
        }

        LiveMonitor.prototype.start = function()
        {
            this.monitor('start');
            
            var obj = this;
            this._refreshHandler = setInterval(function() { obj.monitor('start'); }, this._refreshInterval);
        }

        LiveMonitor.prototype.stop = function()
        {
            clearInterval(this._refreshHandler);
            this._refreshHandler = null;
            
            this.monitor('stop');
        }
    }
    LiveMonitor.initialized = true;
}