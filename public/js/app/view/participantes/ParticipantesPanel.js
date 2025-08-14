Ext.define('EduHack.view.participantes.ParticipantesPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'participantesPanel',

    title: 'Gestión de Participantes',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    scrollable: true,
    bodyPadding: 10,

    items: [
        {
            xtype: 'form',
            title: 'Registrar Nuevo Participante',
            bodyPadding: 10,
            defaults: {
                anchor: '100%',
                labelWidth: 120,
                margin: '5 0'
            },
            items: [
                {
                    xtype: 'combobox',
                    fieldLabel: 'Tipo',
                    name: 'tipo',
                    store: ['estudiante', 'mentor_tecnico'],
                    value: 'estudiante',
                    listeners: {
                        change: function(combo, newValue) {
                            var form = this.up('form');
                            form.query('[cls=estudiante-field]').forEach(function(field) {
                                field.setVisible(newValue === 'estudiante');
                            });
                            form.query('[cls=mentor-field]').forEach(function(field) {
                                field.setVisible(newValue === 'mentor_tecnico');
                            });
                        }
                    }
                },
                { xtype: 'textfield', fieldLabel: 'Nombre', name: 'nombre', allowBlank: false },
                { xtype: 'textfield', fieldLabel: 'Email', name: 'email', vtype: 'email', allowBlank: false },
                { xtype: 'textfield', fieldLabel: 'Nivel de Habilidad', name: 'nivelHabilidad' },

                { xtype: 'textfield', fieldLabel: 'Grado', name: 'grado', cls: 'estudiante-field' },
                { xtype: 'textfield', fieldLabel: 'Instituto', name: 'instituto', cls: 'estudiante-field' },
                { xtype: 'numberfield', fieldLabel: 'Tiempo Disponible (hrs)', name: 'tiempoDisponibleSemanal', cls: 'estudiante-field' },
                { xtype: 'textfield', fieldLabel: 'Habilidades (separadas por coma)', name: 'habilidades', cls: 'estudiante-field' },

                { xtype: 'textfield', fieldLabel: 'Especialidad', name: 'especialidad', cls: 'mentor-field', hidden: true },
                { xtype: 'numberfield', fieldLabel: 'Experiencia (años)', name: 'experiencia', cls: 'mentor-field', hidden: true },
                { xtype: 'textfield', fieldLabel: 'Disponibilidad Horaria', name: 'disponibilidadHoraria', cls: 'mentor-field', hidden: true }
            ],
            buttons: [
                {
                    text: 'Registrar Participante',
                    handler: function() {
                        var form = this.up('form').getForm();
                        if (form.isValid()) {
                            var values = form.getValues();
                            console.log('Datos del participante a enviar:', values);

                            // Lógica para limpiar campos no relevantes según el tipo antes de enviar
                            if (values.tipo === 'estudiante') {
                                delete values.especialidad;
                                delete values.experiencia;
                                delete values.disponibilidadHoraria;
                            } else if (values.tipo === 'mentor_tecnico') {
                                delete values.grado;
                                delete values.instituto;
                                delete values.tiempoDisponibleSemanal;
                                delete values.habilidades;
                            }

                            Ext.Ajax.request({
                                url: 'api/participantes.php', // ¡URL actualizada!
                                method: 'POST',
                                jsonData: values,
                                success: function(response) {
                                    var res = Ext.decode(response.responseText);
                                    if (res.success) {
                                        Ext.Msg.alert('Éxito', 'Participante registrado con ID: ' + res.participante_id);
                                        Ext.ComponentQuery.query('participantesGrid')[0].getStore().reload();
                                        form.reset();
                                    } else {
                                        Ext.Msg.alert('Error', 'No se pudo registrar el participante: ' + res.message);
                                    }
                                },
                                failure: function(response) {
                                    Ext.Msg.alert('Error', 'Error de conexión al registrar participante.');
                                }
                            });
                        }
                    }
                }
            ]
        },

        {
            xtype: 'gridpanel',
            title: 'Lista de Participantes',
            flex: 1,
            margin: '20 0 0 0',
            height: 300,
            cls: 'participantesGrid',
            store: Ext.create('Ext.data.Store', {
                storeId: 'participantesStore',
                fields: [
                    { name: 'id', type: 'int' },
                    'tipo', 'nombre', 'email', 'nivelHabilidad',
                    'grado', 'instituto', 'tiempoDisponibleSemanal', 'habilidades',
                    'especialidad', 'experiencia', 'disponibilidadHoraria'
                ],
                proxy: {
                    type: 'ajax',
                    url: 'api/participantes.php',
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
                { text: 'Nombre', dataIndex: 'nombre', flex: 1 },
                { text: 'Email', dataIndex: 'email', flex: 1 },
                { text: 'Habilidad', dataIndex: 'nivelHabilidad', width: 120 },
                {
                    text: 'Detalles Específicos',
                    flex: 2,
                    renderer: function(value, metaData, record) {
                        if (record.get('tipo') === 'estudiante') {
                            return 'Grado: ' + record.get('grado') + ', Inst: ' + record.get('instituto') + ', Disp: ' + record.get('tiempoDisponibleSemanal') + 'hrs, Habs: ' + record.get('habilidades');
                        } else if (record.get('tipo') === 'mentor_tecnico') {
                            return 'Especialidad: ' + record.get('especialidad') + ', Exp: ' + record.get('experiencia') + ' años, Disp: ' + record.get('disponibilidadHoraria');
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