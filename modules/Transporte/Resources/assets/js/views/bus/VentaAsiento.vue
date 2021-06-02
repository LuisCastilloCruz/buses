<template>
    <el-dialog title="Venta" :visible="visible" @close="onClose" @open="onCreate" :close-on-click-modal="false" width="800px">
        <div class="row">

            <div class="col-5">
                <div class="form-group">
                    <label for="dni">Pasajero</label>
                    <el-select v-model="pasajeroId" filterable remote  popper-class="el-select-customers"
                        placeholder="Buscar remitente"
                        :remote-method="searchPasajero"
                        :loading="loadingPasajero"
                        :disabled=" (transportePasaje) ? true : false"
                        >
                        <el-option v-for="pasaje in pasajeros" :key="pasaje.id" :value="pasaje.id" :label="pasaje.name">

                        </el-option>
                    </el-select>
                </div>
            </div>
            <!-- <div class="col-3">
                <div class="form-group">
                    <label for="dni">Serie</label>
                    <el-input v-model="serie"></el-input>
                </div>
            </div> -->
            <div class="col-3">
                <div class="form-group">
                    <label for="dni">Asiento</label>
                    <el-input disabled :value="asiento.numero_asiento"></el-input>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <div class="form-group">
                    <label for="dni">Estado de asiento</label>
                    <el-select v-model="estadoAsiento"  popper-class="el-select-customers"
                        placeholder="Estado asiento"
                        :disabled=" (transportePasaje) ? true : false"
                        >
                        <el-option v-for="estado in estadosAsientos" :key="estado.id" :value="estado.id" :label="estado.nombre">

                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="dni">Precio</label>
                    <el-input v-model="precio" type="number" :disabled=" (transportePasaje) ? true : false"></el-input>
                </div>
            </div>
            <div v-if="transportePasaje" class="col-3">
                <el-button type="primary" @click="viewComprobante" :style="{marginTop:'1.90rem'}">
                    Comprobante
                    <i class="fa fa-file-alt"></i>
                </el-button>
            </div>
        </div>
        <div v-if="!transportePasaje" class="row mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info">
                        Pago
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Tipo de comprobante</label>
                                    <el-select
                                        v-model="document.document_type_id"
                                        @change="changeDocumentType"
                                        popper-class="el-select-document_type"
                                        dusk="document_type_id"
                                        class="border-left rounded-left border-info"
                                    >
                                        <el-option
                                        v-for="option in documentTypesInvoice"
                                        :key="option.id"
                                        :value="option.id"
                                        :label="option.description"
                                        ></el-option>
                                    </el-select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Serie</label>
                                    <!-- <el-input v-model="document.serie" disabled></el-input> -->
                                    <el-select v-model="document.series_id">
                                        <el-option
                                        v-for="option in allSeries"
                                        :key="option.id"
                                        :value="option.id"
                                        :label="option.number"
                                        ></el-option>
                                    </el-select>
                                </div>
                            </div>
                            <!-- <div class="col-3">
                                    <div class="form-group">
                                    <label for="">Fecha emisión</label>
                                    <el-date-picker
                                    v-model="document.date_of_issue"
                                    type="date"
                                    value-format="yyyy-MM-dd"
                                    placeholder="Fecha de vencimiento">
                                    </el-date-picker>
                                </div>
                            </div>
                            <div class="col-3">
                                    <div class="form-group">
                                    <label for="">Fecha de vencimiento</label>
                                    <el-date-picker
                                    
                                    v-model="document.date_of_due"
                                    type="date"
                                    value-format="yyyy-MM-dd"
                                    placeholder="Fecha de vencimiento">
                                    </el-date-picker>
                                </div>
                            </div> -->
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <table class="table table-bordered table-stripped">
                                    <thead>
                                        <th>M. Pago</th>
                                        <th>Destino</th>
                                        <th>Referencia</th>
                                        <!-- <th>Monto</th> -->
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td>
                                                <div class="form-group mb-2 mr-2">
                                                    <el-select v-model="payment.payment_method_type_id">
                                                        <el-option
                                                        v-for="option in paymentMethodTypes"
                                                        :key="option.id"
                                                        :value="option.id"
                                                        :label="option.description"
                                                        ></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group mb-2 mr-2">
                                                    <el-select
                                                        v-model="payment.payment_destination_id"
                                                        filterable
                                                        :disabled="payment.payment_destination_disabled"
                                                    >
                                                        <el-option
                                                        v-for="option in paymentDestinations"
                                                        :key="option.id"
                                                        :value="option.id"
                                                        :label="option.description"
                                                        ></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group mb-2 mr-2">
                                                    <el-input v-model="payment.reference"></el-input>
                                                </div>
                                            </td>
                                            <!-- <td>
                                                <div class="form-group mb-2 mr-2">
                                                    <el-input disabled v-model="payment.payment"></el-input>
                                                </div>
                                            </td> -->
                                        </tr>

                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <document-options
        :showDialog.sync="showDialogDocumentOptions"
        :recordId="documentId"
        :isContingency="false"
        :showClose="true"
        ></document-options>
        

        

        <div class="row mt-2">
            <div class="col-12 d-flex justify-content-center">
                <el-button :disabled=" (transportePasaje) ? true : false" :loading="loading" type="primary" @click="realizarVenta">Guardar</el-button>
                <el-button type="secondary" @click="onClose">Cancelar</el-button>
            </div>
        </div>
    </el-dialog>
</template>
<script>
import { exchangeRate } from '../../../../../../../resources/js/mixins/functions';
import DocumentOptions from "@views/documents/partials/options.vue";
export default {
    mixins: [exchangeRate],
    components:{
        DocumentOptions
    },
    props:{
        visible: {
            type: Boolean,
            required: true,
            default: false,
        },
        asiento: {
            type: Object,
            required: true,
            default: {},
        },
        programacion: {
            type: Object | null,
            required: true,
            default: {},
        },
        estadosAsientos:{
            type:Array,
            required:true,
            default:() => []
        },
        fechaSalida: {
            type: String,
            required: true,
            default: '',
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
        serie:{
            type:Number | null,
            required:true
        }
    },
    created(){
        this.all_document_types = this.documentTypesInvoice;
        this.allSeries = this.series;
    },
    watch:{
        precio:function(newVal){
            if(this.document.payments.length > 0){
                this.document.payments[0].payment = newVal ? newVal : 0;
            }
        }
    },
    data(){
        return ({
            pasajeros:[],
            pasajero:null,
            loadingPasajero:false,
            

            estadoAsiento:null,
            pasajeroId:null,
            precio:null,

            loading:false,
            transportePasaje:null,

            //document
            documentId:null,
            document: {
                payments: [],
            },
            producto:{},
            all_document_types: [],
            document_types: [],
            allSeries:[],
            showDialogDocumentOptions:false,
            payment:{
                payment_method_type_id:null,
                payment_destination_id:null,
                reference:null,
                payment:null
            }

        });
    },
    methods:{
        onClose(){
            this.$emit("update:visible", false);
            
            this.pasajeroId = null;
            this.estadoAsiento = null;
            this.precio = null;
        },
        async searchPasajero(input=''){
            this.loadingPasajero = true;
            const { data } = await this.$http.get(`/transportes/encomiendas/get-clientes?search=${input}`);
            this.loadingPasajero = false;
            this.pasajeros = data.clientes;
        },
        async onCreate(){
            this.estadoAsiento = 2;
            this.transportePasaje = this.asiento.transporte_pasaje || null;
            await this.searchPasajero();
            this.initForm();
            this.initProducto();
            this.initDocument();
            this.clickAddPayment();
            this.onCalculateTotals();
            this.validateIdentityDocumentType();
            const date = moment().format("YYYY-MM-DD");
            await this.searchExchangeRateByDate(date).then((res) => {
                this.document.exchange_rate_sale = res;
            });
            
            if(this.transportePasaje){
                this.pasajero = this.transportePasaje.pasajero;
                this.precio = this.transportePasaje.precio;
                this.pasajeroId = this.pasajero.id;
                this.estadoAsiento = this.transportePasaje.estado_asiento_id;
                this.documentId = this.transportePasaje.document_id;
            }
        },

        async saveDocument(){
            if(!this.precio) return;
            this.producto.total = this.producto.total_value = this.producto.unit_price = parseFloat(this.precio);
            this.document.items.push(this.producto);
            this.document.payments.push(this.payment);
            this.onCalculateTotals();

            let validate_payment_destination = this.validatePaymentDestination();
            if (validate_payment_destination.error_by_item > 0) {
                return this.$message.error("El destino del pago es obligatorio");
            }

            await this.$http
                .post(`/documents`, this.document)
                .then(async (response) => {
                    if (response.data.success) {
                        this.documentId = response.data.data.id;
                        this.form_cash_document.document_id = response.data.data.id;
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        this.$message.error(error.response.data.message);
                    }
                })
                .finally(() => {
                });

            await this.saveCashDocument();

        },
        async realizarVenta(){
            this.loading = true;
            await this.saveDocument();
            let data = {
                document_id:this.documentId,
                pasajero_id:this.pasajeroId,
                asiento_id:this.asiento.id,
                estado_asiento_id:this.estadoAsiento,
                serie:this.serie,
                programacion_id:this.programacion.id,
                fecha_salida:this.fechaSalida,
                precio:this.precio
            };
            
            this.$http.post('/transportes/sales/realizar-venta-boleto',data)
            .then( ({data}) => {
                this.loading = false;
                this.$emit('onSuccessVenta',this.documentId);
                this.$message({
                    type: 'success',
                    message: data.message
                });
                this.onClose()
            }).catch( error => {
                this.axiosError(error);
            }).finally(() => {
                this.loading = false;
            });
        },
        //document and payment
        initProducto(){
            this.producto = {
                item_id:1,
                codigo_interno: '',
                description: '',
                codigo_producto_sunat: '',
                unidad_de_medida: 'ZZ',
                quantity: 1,
                unit_value: 0,
                price_type_id: "01",
                unit_price: 0,
                affectation_igv_type_id: "10",
                total_base_igv: 20,
                percentage_igv: 18,
                total_igv: 3.6,
                system_isc_type_id:null,
                total_taxes:0,
                total_value:0,
                total:0,
                total_plastic_bag_taxes: 0,
                // total_impuestos: 3.6,
                // total_valor_item: 0,
                // total_item: 0,
                discounts:[],
                total_discount:0,
                total_charge:0,
                charges:[],
                item:{
                    id:1,
                    full_description:"Pasaje item" ,
                    brand:null,
                    category:null,
                    stock:null,
                    internal_id:null,
                    description:"Pasaje item",
                    currency_type_id:"PEN",
                    currency_type_symbol:"S/",
                    sale_unit_price:20,
                    purchase_unit_price:0.000000,
                    unit_type_id:"ZZ",
                    sale_affectation_igv_type_id:10,
                    purchase_affectation_igv_type_id:10,
                    calculate_quantity:false,
                    has_igv:true,
                    amount_plastic_bag_taxes:0.10,
                    item_unit_types:[],
                    warehouses:[

                    ]
                }
            };
        },
        initDocument() {
            this.document = {
                customer_id: 1,
                customer: {},
                document_type_id: null,
                series_id: this.series,
                establishment_id: 1,
                number: "#",
                date_of_issue: moment().format("YYYY-MM-DD"),
                time_of_issue: moment().format("HH:mm:ss"),
                currency_type_id: "PEN",
                purchase_order: null,
                exchange_rate_sale: 0,
                total_prepayment: 0,
                total_charge: 0,
                total_discount: 0,
                total_exportation: 0,
                total_free: 0,
                total_taxed: 0,
                total_unaffected: 0,
                total_exonerated: 0,
                total_igv: 0,
                total_base_isc: 0,
                total_isc: 0,
                total_base_other_taxes: 0,
                total_other_taxes: 0,
                total_taxes: 0,
                total_value: 0,
                total: 0,
                operation_type_id: "0101",
                date_of_due: moment().format("YYYY-MM-DD"),
                delivery_date: moment().format("YYYY-MM-DD"),
                items: [],
                charges: [],
                discounts: [],
                attributes: [],
                guides: [],
                additional_information: null,
                actions: {
                    format_pdf: "a4",
                },
                dispatch_id: null,
                dispatch: null,
                is_receivable: false,
                payments: [],
                hotel: {},
            };
        },
        onCalculateTotals() {
            let total_exportation = 0;
            let total_taxed = 0;
            let total_exonerated = 0;
            let total_unaffected = 0;
            let total_free = 0;
            let total_igv = 0;
            let total_value = 0;
            let total = 0;
            let total_plastic_bag_taxes = 0;
            let total_discount = 0;
            let total_charge = 0;
            this.document.items.forEach((row) => {
                total_discount += parseFloat(row.total_discount);
                total_charge += parseFloat(row.total_charge);

                if (row.affectation_igv_type_id === "10") {
                    total_taxed += parseFloat(row.total_value);
                }
                if (["10", "20", "30", "40"].indexOf(row.affectation_igv_type_id) < 0) {
                    total_free += parseFloat(row.total_value);
                }
                if (
                ["10", "20", "30", "40"].indexOf(row.affectation_igv_type_id) > -1
                ) {
                    total_igv += parseFloat(row.total_igv);
                    total += parseFloat(row.total);
                }
                total_value += parseFloat(row.total_value);
                total_plastic_bag_taxes += parseFloat(row.total_plastic_bag_taxes);

                if (["13", "14", "15"].includes(row.affectation_igv_type_id)) {
                    let unit_value =
                        row.total_value / row.quantity / (1 + row.percentage_igv / 100);
                    let total_value_partial = unit_value * row.quantity;
                    row.total_taxes = row.total_value - total_value_partial;
                    row.total_igv = row.total_value - total_value_partial;
                    row.total_base_igv = total_value_partial;
                    total_value -= row.total_value;
                }
            });

            this.document.total_exportation = _.round(total_exportation, 2);
            this.document.total_taxed = _.round(total_taxed, 2);
            this.document.total_exonerated = _.round(total_exonerated, 2);
            this.document.total_unaffected = _.round(total_unaffected, 2);
            this.document.total_free = _.round(total_free, 2);
            this.document.total_igv = _.round(total_igv, 2);
            this.document.total_value = _.round(total_value, 2);
            this.document.total_taxes = _.round(total_igv, 2);
            this.document.total_plastic_bag_taxes = _.round(
                total_plastic_bag_taxes,
                2
            );
            this.document.total = _.round(
                total + this.document.total_plastic_bag_taxes,
                2
            );
        },
        validatePaymentDestination() {
            let error_by_item = 0;

            this.document.payments.forEach((item) => {
                if (item.payment_destination_id == null) error_by_item++;
            });

            return {
                error_by_item: error_by_item,
            };
        },
        initForm() {
            this.form_cash_document = {
                document_id: null,
                sale_note_id: null,
            };
        },
        async onGoToInvoice() {
            // this.onUpdateItemsWithExtras();
            this.onCalculateTotals();
            let validate_payment_destination = this.validatePaymentDestination();

            if (validate_payment_destination.error_by_item > 0) {
                return this.$message.error("El destino del pago es obligatorio");
            }
            // console.log(this.document);
            // return;
            this.loading = true;
            this.$http
                .post(`/${this.resource_documents}`, this.document)
                .then(async (response) => {
                    if (response.data.success) {
                        this.encomienda.document_id = response.data.data.id;
                        this.form_cash_document.document_id = response.data.data.id;
                        this.showDialogDocumentOptions = true;
                        this.$emit("update:showDialog", false);
                        await this.onStore();// guardando pasajes
                        await this.saveCashDocument();
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        this.$message.error(error.response.data.message);
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveCashDocument() {
            this.$http
                .post(`/cash/cash_document`, this.form_cash_document)
                .then((response) => {
                    if (!response.data.success) {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    this.axiosError(error);
                });
        },
        changeDocumentType() {
            this.document.series_id = null;
            this.allSeries = _.filter(this.series, {
                document_type_id: this.document.document_type_id,
            });
            this.document.series_id = this.allSeries.length > 0 ? this.allSeries[0].id : null;
        },
        clickAddPayment() {
            const payment = this.document.payments.length == 0 ? this.document.total : 0;
            const paymentDestinationId = this.paymentDestinations.length > 0 ? this.paymentDestinations[0].id : null;

            this.payment = {
                id: null,
                document_id: null,
                date_of_payment: moment().format("YYYY-MM-DD"),
                payment_method_type_id: "01",
                payment_destination_id: paymentDestinationId,
                reference: null,
                payment: payment,
            };
            // this.document.payments.push({
            //     id: null,
            //     document_id: null,
            //     date_of_payment: moment().format("YYYY-MM-DD"),
            //     payment_method_type_id: "01",
            //     payment_destination_id: null,
            //     reference: null,
            //     payment: payment,
            // });
        },
        validateIdentityDocumentType() {
            let identity_document_types = ["0", "1"];
            let customer = this.document.customer;
            if (
                identity_document_types.includes(customer.identity_document_type_id)
            ) {
                this.document_types = _.filter(this.all_document_types, { id: "03" });
            } else {
                this.document_types = this.all_document_types;
            }

            this.document.document_type_id =
                this.document_types.length > 0 ? this.document_types[0].id : null;
            this.changeDocumentType();
        },
        viewComprobante(){
            this.showDialogDocumentOptions = true;
        }

    }
}
</script>