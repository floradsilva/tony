(function($) {

    var self;

    AW.Settings = {

        params: {},

        init: function() {
            self.params = automatewooSettingsLocalizeScript;
            self.initSwitchToOptinModeWarning();
        },


        initSwitchToOptinModeWarning: function() {
            var $field = $('#automatewoo_optin_mode');

            $field.change(function( a, b ) {
                if ( $(this).val() === 'optin' ) {
                    alert( self.params.messages.switchToOptinWarning );
                }
            });
        }

    };

    self = AW.Settings;
    self.init();

})(jQuery);
