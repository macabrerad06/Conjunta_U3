Ext.define('EduHack.view.equipos.EquiposPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'equiposPanel',

    title: 'Gestión de Equipos',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    scrollable: true,
    bodyPadding: 10,

    items: [
        {
            xtype: 'form',
            title: 'Crear Nuevo Equipo',
            bodyPadding: 10,
            defaults: {
                anchor: '100%',
                labelWidth: 150,
                margin: '5 0'
            },
            items: [
                { xtype: 'textfield', fieldLabel: 'Nombre del Equipo', name: 'nombre', allowBlank: false },
                { xtype: 'textfield', fieldLabel: 'ID del Hackathon', name: 'hackathon_id', allowBlank: false },
                {
                    xtype: 'textfield',
                    fieldLabel: 'IDs de Participantes (coma)',
                    name: 'participante_ids',
                    emptyText: 'Ej: 1,2,3 - La gestión de estos IDs es manual',
                    tooltip: 'Este campo es de texto. En un sistema real, se usaría una tabla pivote para los miembros del equipo.'
                }
            ],
            buttons: [
                {
                    text: 'Crear Equipo',
                    handler: function() {
                        var form = this.up('form').getForm();
                        if (form.isValid()) {
                            var values = form.getValues();
                            console.log('Datos del equipo a enviar:', values);

                            Ext.Ajax.request({
                                url: 'api/equipos.php', // ¡URL actualizada!
                                method: 'POST',
                                jsonData: values,
                                success: function(response) {
                                    var res = Ext.decode(response.responseText);
                                    if (res.success) {
                                        Ext.Msg.alert('Éxito', 'Equipo creado con ID: ' + res.idEquipo);
                                        Ext.ComponentQuery.query('equiposGrid')[0].getStore().reload();
                                        form.reset();
                                    } else {
                                        Ext.Msg.alert('Error', 'No se pudo crear el equipo: ' + res.message);
                                    }
                                },
                                failure: function(response) {
                                    Ext.Msg.alert('Error', 'Error de conexión al crear equipo.');
                                }
                            });
                        }
                    }
                }
            ]
        },

        {
            xtype: 'form',
            title: 'Asignar Reto a Equipo',
            bodyPadding: 10,
            margin: '20 0 0 0',
            defaults: {
                anchor: '100%',
                labelWidth: 150,
                margin: '5 0'
            },
            items: [
                { xtype: 'numberfield', fieldLabel: 'ID del Equipo', name: 'equipo_id', allowBlank: false },
                { xtype: 'numberfield', fieldLabel: 'ID del Reto', name: 'reto_id', allowBlank: false }
            ],
            buttons: [
                {
                    text: 'Asignar Reto',
                    handler: function() {
                        var form = this.up('form').getForm();
                        if (form.isValid()) {
                            var values = form.getValues();
                            console.log('Asignando reto a equipo:', values);

                            Ext.Ajax.request({
                                url: 'api/equipo_reto.php',
                                method: 'POST',
                                jsonData: values,
                                success: function(response) {
                                    var res = Ext.decode(response.responseText);
                                    if (res.success) {
                                        Ext.Msg.alert('Éxito', 'Reto asignado al equipo.');
                                    } else {
                                        Ext.Msg.alert('Error', 'No se pudo asignar el reto: ' + res.message);
                                    }
                                },
                                failure: function(response) {
                                    Ext.Msg.alert('Error', 'Error de conexión al asignar reto.');
                                }
                            });
                        }
                    }
                }
            ]
        },

        {
            xtype: 'gridpanel',
            title: 'Lista de Equipos',
            flex: 1,
            margin: '20 0 0 0',
            height: 300,
            cls: 'equiposGrid',
            store: Ext.create('Ext.data.Store', {
                storeId: 'equiposStore',
                fields: ['idEquipo', 'nombre', 'hackathon_id', 'participante_ids'],
                proxy: {
                    type: 'ajax',
                    url: 'api/equipos.php',
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        successProperty: 'success'
                    }
                },
                autoLoad: true
            }),
            columns: [
                { text: 'ID Equipo', dataIndex: 'idEquipo', width: 80 },
                { text: 'Nombre', dataIndex: 'nombre', flex: 1 },
                { text: 'ID Hackathon', dataIndex: 'hackathon_id', width: 120 },
                { text: 'IDs Participantes', dataIndex: 'participante_ids', flex: 1 }
            ]
        }
    ]
});