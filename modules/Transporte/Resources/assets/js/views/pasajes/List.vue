<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>LISTADO DE PASAJES VENDIDOS</span></li>
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
        <div v-if="!sale"  class="card mb-0">
            <div class="card-header bg-info text-center">
                
                <h3 class="my-0">Listado de pasajes</h3>
            </div>
            <div  class="card-body">
                <div v-loading="loading" class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Fecha y hora salida </th>
                            <th>Cliente</th>
                            <th>Número</th>
                            <th>Estado Sunat</th>
                            <th>T.Gravado</th>
                            <th>T.Igv</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="pasaje in listPasajes" :key="pasaje.id">
                            <td class="text-right">{{ pasaje.id }}</td>
                            <td>{{ pasaje.fecha_salida }}</td>
                            <td>{{ pasaje.pasajero.name }}</td>
                            <td>{{ pasaje.document.series }}-{{pasaje.document.number}}</td>

                            <td>
                                <el-tooltip v-if="tooltip(pasaje.document, false)"  class="item" effect="dark" placement="bottom">
                                    <div slot="content">{{tooltip(pasaje.document)}}</div>
                                    <span class="badge bg-secondary text-white" :class="{'bg-danger': (pasaje.document.state_type_id === '11'), 'bg-warning': (pasaje.document.state_type_id === '13'), 'bg-secondary': (pasaje.document.state_type_id === '01'), 'bg-info': (pasaje.document.state_type_id === '03'), 'bg-success': (pasaje.document.state_type_id === '05'), 'bg-secondary': (pasaje.document.state_type_id === '07'), 'bg-dark': (pasaje.document.state_type_id === '09')}">
                                        {{pasaje.document.state_type_description}}
                                    </span>
                                </el-tooltip>
                                <span v-else class="badge bg-secondary text-white" :class="{'bg-danger': (pasaje.document.state_type_id === '11'), 'bg-warning': (pasaje.document.state_type_id === '13'), 'bg-secondary': (pasaje.document.state_type_id === '01'), 'bg-info': (pasaje.document.state_type_id === '03'), 'bg-success': (pasaje.document.state_type_id === '05'), 'bg-secondary': (pasaje.document.state_type_id === '07'), 'bg-dark': (pasaje.document.state_type_id === '09')}">
                                    {{pasaje.document.state_type_description}}
                                </span>
                                <template v-if="pasaje.document.regularize_shipping && pasaje.document.state_type_id === '01'">
                                    <el-tooltip class="item" effect="dark" :content="pasaje.document.message_regularize_shipping" placement="top-start">
                                        <i class="fas fa-exclamation-triangle fa-lg" style="color: #d2322d !important"></i>
                                    </el-tooltip>
                                </template>
                            </td>
                            <td>{{ pasaje.document.total_taxed }}</td>
                            <td>{{ pasaje.document.total_igv }}</td>
                            <td>{{ pasaje.document.total }}</td>
                            <!-- <td>{{ pasaje.document }}</td> -->
                            <!-- <td>{{ item.categoria }}</td> -->
                            <td class="text-center">
                                <el-tooltip class="item" effect="dark" content="Editar" placement="top-start">
                                    <el-button type="success" @click="onEdit(pasaje)">
                                        <i class="fa fa-edit"></i>
                                    </el-button>
                                </el-tooltip>

                                <el-tooltip class="item" effect="dark" content="Imprimir comprobante" placement="top-start">
                                    <el-button type="primary" @click="verComprobante(pasaje)">
                                        <i class="fa fa-file-alt"></i>
                                    </el-button>
                                </el-tooltip>

                                <el-tooltip class="item" effect="dark" content="Anular" placement="top-start">
                                    <el-button type="danger" @click="anular(pasaje)">
                                        <i class="fa fa-trash"></i>
                                    </el-button>
                                </el-tooltip>  
                                
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
        <!-- <sales v-else 
            :item-pasajero="pasaje"
            :list-programaciones="listProgramaciones"
            :terminal="userTerminal"
            :estado-asientos="estadoAsientos"
            :establishment="establishment"
            :series="series"
            :document-types-invoice="documentTypesInvoice"
            :payment-method-types="paymentMethodTypes"
            :payment-destinations="paymentDestinations"
            :configuration="configuration"
            :sale.sync="sale"
            @onSuccessVenta="onSuccessVenta"
        /> -->
        <document-options
            :showDialog.sync="showDialogDocumentOptions"
            :recordId="documentNewId"
            :isContingency="false"
            :showClose="true"
            :configuration="configuration"
        ></document-options>
        <documents-voided 
        :showDialog.sync="showDialogVoided"
        :recordId="recordId"></documents-voided>
    </div>
</template>

<script>
import DocumentOptions from "@views/documents/partials/options.vue";
import DocumentsVoided from '@views/documents/partials/voided.vue'
import Sales from './Sales.vue';
export default {
    props: {
        listProgramaciones:{
            type:Array,
            default:() => []
        },
        pasajes:{
            type:Array,
            // required:true,
            default:() => []
        },
        establishment:{
            type:Object,
            required:true
        },
        series:{
            type:Array,
            default:() => []
        },
        documentTypesInvoice:{
            type:Array,
            default:() => []
        },
        paymentMethodTypes: {
            type: Array,
            required: true,
        },
        paymentDestinations: {
            type: Array,
            required: true,
        },
        userTerminal:{
            type:Object,
            default:{}
        },
        configuration:{
            type: Object,
            required: true,
        },
        estadoAsientos:{
            type:Array,
            required:true,
            default:() => []
        },
    },
    components: {
        DocumentOptions,
        DocumentsVoided,
        Sales
    },
    created(){
        this.listPasajes = this.pasajes;
        this.$eventHub.$on('reloadData',async() => {
            this.cancelarBoleto();
        });
        this.getPasajes();
    },
    data() {
        return {
            sale:false,
            recordId:null,
            pasajeId:null,
            showDialogVoided:false,
            listPasajes: [],
            openModalAddEdit: false,
            pasaje:null,
            loading: false,
            edit:false,
            documentNewId:null,
            showDialogDocumentOptions:false,
        };
    },
    mounted() {
    },
    methods: {

        async getPasajes(){
            try{
                // const loading = this.$loading({
                //     lock: false,
                //     text: '',
                //     spinner: 'el-icon-loading',
                //     // background: 'rgba(0, 0, 0, 0.7)'
                // });
                this.loading = true;
                const { data } = await this.$http.get('/transportes/pasajes/get-pasajes');
                this.loading = false;
                this.listPasajes = data;


            }catch(error){
                this.loading = false;
                if(error.response) this.axiosError(error);

            }
        },
        async onSuccessVenta(documentId){
            this.documentId = documentId;
            this.showDialogDocumentOptions = true;
            this.getPasajes();
        },
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
        onEdit(pasaje) {
            window.location.href = `/transportes/sales/${pasaje.id}`;
        },
        onUpdateItem(pasaje) {
            // console.log(pasaje);
            this.items = this.listPasajes.map((i) => {
                if (i.id === pasaje.id) {
                    return pasaje;
                }
                return i;
            });
            this.edit = false;
        },
        onAddItem(pasaje) {
            this.documentNewId = pasaje.document_id;
            this.showDialogDocumentOptions = true;
            this.listPasajes.unshift(pasaje);
        },
        onCreate() {
            // this.sale = true;
            window.location.href="/transportes/sales";
        },
        verComprobante(pasaje){
            this.documentNewId = pasaje.document_id;
            this.showDialogDocumentOptions = true;
        },
        tooltip(row, message = true) {
            if (message) {
                if (row.shipping_status) return row.shipping_status.message;

                if (row.sunat_shipping_status) return row.sunat_shipping_status.message;

                if (row.query_status) return row.query_status.message;
            }

            if ((row.shipping_status) || (row.sunat_shipping_status) || (row.query_status)) return true;

            return false;
        },

        anular(pasaje) {

            this.$confirm(`¿Estás seguro de anular el boleto ?`, 'Atención', {
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'No, cerrar',
                type: 'warning'
            }).then(() => {

                this.pasajeId = pasaje.id;
                this.recordId = pasaje.document.id;
                this.showDialogVoided = true;
                
                
            });
            
        },


        async cancelarBoleto(){
            try{
                const { data } = await axios.delete(`/transportes/pasajes/${this.pasajeId}/delete`);

                if(!data.success){
                    this.$message({
                        type: 'error',
                        message: data.message
                    });
                }

                this.$message({
                    type: 'success',
                    message: data.message
                });

                this.listPasajes = this.listPasajes.filter(i => i.id !== this.pasajeId);
                
            }catch(error){

                this.$message({
                    type: 'error',
                    message: 'Lo sentimos ha ocurrido un error'
                });

            }
        }
    },
};
</script>
