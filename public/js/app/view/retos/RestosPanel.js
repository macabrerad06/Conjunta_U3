Ext.define('EduHack.view.retos.RetosPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'retosPanel',

    title: 'Gestión de Retos',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    scrollable: true,
    bodyPadding: 10,

    items: [
        {
            xtype: 'form',
            title: 'Crear Nuevo Reto',
            bodyPadding: 10,
            defaults: {
                anchor: '100%',
                labelWidth: 150,
                margin: '5 0'
            },
            items: [
                {
                    xtype: 'combobox',
                    fieldLabel: 'Tipo de Reto',
                    name: 'tipo',
                    store: ['real', 'experimental'],
                    value: 'real',
                    listeners: {
                        change: function(combo, newValue) {
                            var form = this.up('form');
                            form.query('[cls=reto-real-field]').forEach(function(field) {
                                field.setVisible(newValue === 'real');
                            });
                            form.query('[cls=reto-experimental-field]').forEach(function(field) {
                                field.setVisible(newValue === 'experimental');
                            });
                        }
                    }
                },
                { xtype: 'textfield', fieldLabel: 'Título', name: 'titulo', allowBlank: false },
                { xtype: 'textarea', fieldLabel: 'Descripción', name: 'descripcion', height: 80 },
                { xtype: 'textfield', fieldLabel: 'Dificultad', name: 'dificultad' },
                { xtype: 'textfield', fieldLabel: 'Áreas de Conocimiento (coma)', name: 'areasConocimiento' },

                { xtype: 'textfield', fieldLabel: 'Entidad Colaboradora', name: 'entidadColaboradora', cls: 'reto-real-field' },

                { xtype: 'textfield', fieldLabel: 'Enfoque Pedagógico', name: 'enfoquePedagogico', cls: 'reto-experimental-field', hidden: true }
            ],
            buttons: [
                {
                    text: 'Crear Reto',
                    handler: function() {
                        var form = this.up('form').getForm();
                        if (form.isValid()) {
                            var values = form.getValues();
                            console.log('Datos del reto a enviar:', values);

                            // Lógica para limpiar campos no relevantes según el tipo
                            if (values.tipo === 'real') {
                                delete values.enfoquePedagogico;
                            } else if (values.tipo === 'experimental') {
                                delete values.entidadColaboradora;
                            }

                            // Llamada AJAX al backend PHP
                            Ext.Ajax.request({
                                url: 'api/retos.php', // ¡URL actualizada!
                                method: 'POST',
                                jsonData: values,
                                success: function(response) {
                                    var res = Ext.decode(response.responseText);
                                    if (res.success) {
                                        Ext.Msg.alert('Éxito', 'Reto creado con ID: ' + res.reto_id);
                                        Ext.ComponentQuery.query('retosGrid')[0].getStore().reload();
                                        form.reset();
                                    } else {
                                        Ext.Msg.alert('Error', 'No se pudo crear el reto: ' + res.message);
                                    }
                                },
                                failure: function(response) {
                                    Ext.Msg.alert('Error', 'Error de conexión al crear reto.');
                                }
                            });
                        }
                    }
                }
            ]
        },

        {
            xtype: 'gridpanel',
            title: 'Lista de Retos',
            flex: 1,
            margin: '20 0 0 0',
            height: 300,
            cls: 'retosGrid',
            store: Ext.create('Ext.data.Store', {
                storeId: 'retosStore',
                fields: [
                    { name: 'id', type: 'int' },
                    'tipo', 'titulo', 'descripcion', 'dificultad', 'areasConocimiento',
                    'entidadColaboradora', 'enfoquePedagogico'
                ],
                proxy: {
                    type: 'ajax',
                    url: 'api/retos.php',
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        successProperty: 'success'
                    }
                },
                autoLoad: true
            }),
            columns: [
                { text: 'ID', dataIndex: 'id', width: 50 },
                { text: 'Tipo', dataIndex: 'tipo', width: 100 },
                { text: 'Título', dataIndex: 'titulo', flex: 1 },
                { text: 'Dificultad', dataIndex: 'dificultad', width: 100 },
                {
                    text: 'Detalles Específicos',
                    flex: 2,
                    renderer: function(value, metaData, record) {
                        if (record.get('tipo') === 'real') {
                            return 'Entidad Colaboradora: ' + record.get('entidadColaboradora');
                        } else if (record.get('tipo') === 'experimental') {
                            return 'Enfoque Pedagógico: ' + record.get('enfoquePedagogico');
                        }
                        return '';
                    }
                }
            ]
        }
    ],
    initComponent: function() {
        this.callParent(arguments);
        var form = this.down('form');
        var tipoField = form.down('[name=tipo]');
        tipoField.fireEvent('change', tipoField, tipoField.getValue());
    }
});