<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>REGISTRO DE ENCOMIENDAS</span></li>
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
                <h3 class="my-0">Listado de encomiendas</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th></th>

                            <th>Remitente</th>
                            <th>Destinatario</th>
                            <th>Fecha salida</th>
                            <th>Hora salida</th>
                            <th>Estado envio</th>
                            <!-- <th>Categoría</th> -->
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="encomienda in listEncomiendas" :key="encomienda.id">
                            <td class="text-right">{{ encomienda.id }}</td>
                            <td>{{ encomienda.remitente.name }}</td>
                            <td>{{ encomienda.destinatario.name }}</td>
                            <td>{{ encomienda.fecha_salida }}</td>
                            <td>{{ encomienda.programacion.hora_salida }}</td>
                            <td>{{ encomienda.estado_envio.nombre }}</td>
                            <!-- <td>{{ item.categoria }}</td> -->
                            <td class="text-center">
                                <el-button type="success" @click="onEdit(encomienda)">
                                    <i class="fa fa-edit"></i>
                                </el-button>
                                <el-button type="danger" @click="onDelete(encomienda)">
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
            :item-encomienda="encomienda"
            :estados-envio="estadosEnvio"
            :estados-pago="estadosPago"
            :edit="edit"
        ></ModalAddEdit>
    </div>
</template>

<script>
import ModalAddEdit from "./AddEdit";

export default {
    props: {
        encomiendas:{
            type:Array,
            required:true,
            default:() => []
        },
        estadosPago:{
            type:Array,
            required:true,
        },
        estadosEnvio:{
            type:Array,
            required:true,
        }
    },
    components: {
        ModalAddEdit,
    },
    created(){
        this.listEncomiendas = this.encomiendas;
    },
    data() {
        return {
            listEncomiendas: [],
            openModalAddEdit: false,
            encomienda:null,
            loading: false,
            edit:false
        };
    },
    mounted() {
    },
    methods: {
        onDelete(item) {
            this.$confirm(`¿estás seguro de eliminar al elemento ${item.nombre}?`, 'Atención', {
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'No, cerrar',
                type: 'warning'
            }).then(() => {
                this.$http.delete(`/transportes/choferes/${item.id}/delete`).then(response => {
                    this.$message({
                        type: 'success',
                        message: response.data.message
                    });
                    this.items = this.items.filter(i => i.id !== item.id);
                }).catch(error => {
                    this.axiosError(error)
                });
            }).catch();
        },
        onEdit(encomienda) {
            this.edit = true;
            this.encomienda = { ...encomienda };
            this.edit = true;
            this.openModalAddEdit = true;
        },
        onUpdateItem(encomienda) {
            this.items = this.listEncomiendas.map((i) => {
                if (i.id === encomienda.id) {
                    return encomienda;
                }
                return i;
            });
        },
        onAddItem(encomienda) {
            this.listEncomiendas.unshift(encomienda);
        },
        onCreate() {
            this.edit = false;
            this.encomienda = null;
            this.openModalAddEdit = true;
        },
    },
};
</script>
