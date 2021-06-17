<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>REGISTRO DE PROGRAMACIONES</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <div class="btn-group flex-wrap">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm mt-2 mr-2"
                        @click="onCreate"
                    >
                        <i class="fa fa-plus-circle"></i> Nuevo
                    </button>
                </div>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="my-0">Listado de programaciones</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Vehiculo</th>
                            <!-- <th>Fecha Salida</th> -->
                            <th>Hora Salida</th>
                            <!-- <th>Tiempo aproximado</th> -->
                            <!-- <th>Ciudad</th> -->
                            <th></th>
                            <!-- <th>Licencia</th>
                            <th>Categoría</th> -->
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="programacion in listProgramaciones" :key="programacion.id">
                            <td class="text-right">{{ programacion.id }}</td>
                            <td>{{ programacion.origen.nombre }}</td>
                            <td>{{ programacion.destino.nombre }}</td>
                            <td>{{ programacion.vehiculo.placa }}</td>
                            <td>{{ programacion.hora_view }}</td>
                            <!-- <td>{{ programacion.tiempo_aproximado }} hr</td> -->
                            <!-- <td>{{ item.licencia }}</td>
                            <td>{{ item.categoria }}</td> -->
                            <td class="text-center">
                                <el-button type="primary" @click="onConfigRutas(programacion)">
                                    <i class="fa fa-cogs"></i>
                                </el-button>
                                <!-- <el-tooltip class="item" effect="dark" content="Generar manifiesto" placement="top-start">
                                    <el-button type="secondary" @click="openModalGenerarManifiesto(programacion)">
                                        <i class="fa fa-file"></i>
                                    </el-button>
                                </el-tooltip> -->
                                
                                <el-button type="success" @click="onEdit(programacion)">
                                    <i class="fa fa-edit"></i>
                                </el-button>
                                <el-button type="danger" @click="onDelete(programacion)">
                                    <i class="fa fa-trash"></i>
                                </el-button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <ModalAddEdit
            :visible.sync="openModalAddEdit"
            @onAddItem="onAddItem"
            @onUpdateItem="onUpdateItem"
            :programacion="programacion"
            :terminales="terminales"
            :vehiculos="vehiculos"
            :user-terminal="userTerminal"
        ></ModalAddEdit>
        <!-- Modal para la configuracion de rutas de cada terminal -->
        <configuracion-rutas 
        :terminales="terminales"
        :programacion="programacion" 
        :visible.sync="openModalConfigRutas" 
        @onUpdateItem="onUpdateItem" />
        
        <generar-manifiesto 
        :visible.sync="visible" 
        :series="series" 
        :programacion="programacion"
        :choferes="choferes"
        />
    </div>
</template>

<script>
import ModalAddEdit from "./AddEdit";
import ConfiguracionRutas from './ConfiguracionRutas';
import GenerarManifiesto from './GenerarManifiesto.vue';


export default {
    props: {
        terminales: {
            type: Array,
            required: true,
        },
        programaciones:{
            type: Array,
            required:true,
        },
        vehiculos:{
            type:Array,
            required:true
        },
        series:{
            type:Array,
            default:() => []
        },
        choferes:{
            type:Array,
            default:() => []
        },
        userTerminal:{
            type:Object,
            default:{}
        }
    },
    components: {
        ModalAddEdit,
        ConfiguracionRutas,
        GenerarManifiesto
    },
    created(){
        this.listProgramaciones = this.programaciones;
        this.listVehiculos = this.vehiculos;
    },
    data() {
        return {
            listProgramaciones: [],
            listVehiculos:[],
            programacion: null,
            openModalAddEdit: false,
            openModalConfigRutas:false,
            loading: false,
            visible:false,
        };
    },
    mounted() {
        // this.listTerminales = this.terminales;
    },
    methods: {
        onDelete(item) {
            this.$confirm(`¿Estás seguro de eliminar la programación ?`, 'Atención', {
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'No, cerrar',
                type: 'warning'
            }).then(() => {
                this.$http.delete(`/transportes/programaciones/${item.id}/delete`).then(response => {
                this.$message({
                    type: 'success',
                    message: response.data.message
                });
                this.items = this.items.filter(i => i.id !== item.id);
            }).catch(error => {
                let response = error.response;
                this.$message({
                    type: 'error',
                    message: response.data.message
                });
            });
            })
        },
        onEdit(programacion) {
            this.programacion = { ...programacion };
            this.openModalAddEdit = true;
        },
        onUpdateItem(programacion) {
            this.listProgramaciones = this.listProgramaciones.map((i) => {
                if (i.id === programacion.id) {
                    return programacion;
                }
                return i;
            });
        },
        onAddItem(programacion) {
            this.listProgramaciones.unshift(programacion);
        },
        onCreate() {
            this.programacion = null;
            this.openModalAddEdit = true;
        },
        onConfigRutas(programacion){
            this.programacion = programacion;
            this.openModalConfigRutas = true;
        },
        openModalGenerarManifiesto(programacion){
            this.programacion = programacion;
            this.visible = true;
        }
    },
};
</script>
