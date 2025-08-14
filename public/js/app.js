
Ext.application({
    name: 'EduHack',
    appFolder: 'js/app',

    requires: [
        'EduHack.view.participantes.ParticipantesPanel',
        'EduHack.view.retos.RetosPanel',
        'EduHack.view.equipos.EquiposPanel'
    ],

    launch: function() {
        Ext.create('Ext.container.Viewport', {
            layout: 'border',
            items: [
                {
                    region: 'north',
                    xtype: 'toolbar',
                    height: 50,
                    items: [
                        {
                            xtype: 'tbtext',
                            text: '<b>EduHack Plataforma</b>',
                            cls: 'x-toolbar-text-default'
                        },
                        '->',
                        {
                            xtype: 'button',
                            text: 'Participantes',
                            handler: function() {
                                var mainPanel = Ext.getCmp('mainContentPanel');
                                mainPanel.getLayout().setActiveItem('participantesPanel');
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'Retos',
                            handler: function() {
                                var mainPanel = Ext.getCmp('mainContentPanel');
                                mainPanel.getLayout().setActiveItem('retosPanel');
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'Equipos',
                            handler: function() {
                                var mainPanel = Ext.getCmp('mainContentPanel');
                                mainPanel.getLayout().setActiveItem('equiposPanel');
                            }
                        }
                    ]
                },
                {
                    region: 'center',
                    xtype: 'panel',
                    id: 'mainContentPanel',
                    layout: 'card',
                    activeItem: 0,
                    items: [
                        { xtype: 'participantesPanel' },
                        { xtype: 'retosPanel' },
                        { xtype: 'equiposPanel' }
                    ]
                }
            ]
        });
    }
});