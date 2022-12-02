<template>
    <div style="width: 100%">
        <div class="card mb-0 page-header pr-0">
            <div class="card-body">
                <div class="row">
                    <!-- piso -->
                    <div class="col-md-12 col-sm-12 pb-2 text-center">
                        <el-button-group>
                            <a href="#" class="btn btn-success px-4 py-2 mr-1" size="medium" :class="AqpTap.active==1 ? 'btn-warning': '' " @click="cargarTap(1)">POS</a>

                            <a href="#" class="btn btn-success px-4 py-2" size="medium" :class="AqpTap.active==2 ? 'btn-warning': '' " @click="cargarTap(2)">MESAS</a>

                            <a href="#" class="btn btn-success px-4 py-2 ml-1" size="medium" :class="AqpTap.active==3 ? 'btn-warning': '' " @click="cargarTap(3)">PEDIDOS</a>

                            <a href="#" class="btn btn-success px-4 py-2 ml-1" :class="AqpTap.active==4 ? 'btn-warning': '' " @click="cargarTap(4)">PRECIOS</a>

                        </el-button-group>
                    </div>
                </div>
                <div class="row">
                    <div v-if="AqpTap.active==1" class="col-md-12">
                        POS
                    </div>
                    <div v-else-if="AqpTap.active==2" class="col-md-12">
                        <el-tabs v-model="activeName" @tab-click="handleClick">

                            <el-tab-pane  v-for="nivel in niveles" :key="nivel.id" :label="nivel.nombre" :name="nivel.nombre">

                                <div class="row px-5">
                                    <div v-if="vistaMesas" class="col-md-8 text-center">
                                        <div :style="{background:controlarEstadosMesas(mesa)}"  v-for="mesa in nivel.mesas" :key="mesa.id" class="el-card box-card is-always-shadow m-4" style="width: 100px; float: left" v-on:click="seleccionaMesa(mesa)">
                                            <div  class="text item">
                                                <h3 class="text-white"><b>{{ mesa.numero }}</b></h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="col-md-8 text-center">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h3 class="font-weight-bold">Estás haciendo pedido para la mesa: <b style="color: #0A7CB5">{{ mesaActivo.numero}}</b></h3>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div v-for="item in items" :key="item.id" class="el-card box-card is-always-shadow m-4 float-left" @click="agregarItem(item)">
                                                <img :src="'/storage/uploads/items/'+item.image_small" class="image" width="150" height="150" style="max-width: 100%">
                                                <div style="padding: 14px;">
                                                    <span class="font-large font-18 font-weight-bold">  S/ {{ item.sale_unit_price }}</span>
                                                    <div class="bottom clearfix">
                                                        <span class="font-medium font-weight-bold"> {{ item.name }}</span>
<!--                                                        <el-button type="text" class="button"><h5></h5>{{item.description}}</el-button>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-4 border-left">
                                        <div class="row text-center">
                                            <div v-if="mesaIsActivo" class="col-md-12 table-responsive px-0">
                                                <h3>Pedidos de la mesa: <b style="color: #0A7CB5">{{ mesaActivo.numero}}</b></h3>
                                                <h5>Clientes Varios</h5>
                                                <div style="width: 100%" v-loading="loading"></div>
                                                <table class="table">
                                                    <tbody>
                                                        <tr v-for="(item, index) in pedidos_detalles" :key="index">
                                                            <td class="py-2">
                                                                <table class="table m-0 m-0" style="border:0px">
                                                                    <tr>
                                                                        <td style="border: 0px;margin: 0; padding: 0" class="text-left">
                                                                            <el-tooltip class="item" effect="dark" content="Disminuir" placement="top-start">
                                                                                <el-button type="warning" icon="el-icon-remove-outline" @click="disminuirCantidad(item)"> </el-button>
                                                                            </el-tooltip>
                                                                            <el-tooltip class="item" effect="dark" content="Incrementar" placement="top-start">
                                                                                <el-button type="success" icon="el-icon-plus" @click="incrementarCantidad(item)"> </el-button>
                                                                            </el-tooltip>
                                                                        </td>

                                                                        <td style="border: 0px;margin: 0; padding: 0" class="text-left">
                                                                            <p style="font-size: 1.5em">
                                                                                <span class="text-blue">S/ {{ item.precio }}</span> x <span style="color:darkgreen">{{ item.cantidad }}</span>
                                                                                {{ item.descripcion }}
                                                                            </p>
                                                                        </td>

                                                                        <td style="border: 0px;margin: 0; padding: 0" class="text-right">
                                                                            <el-tooltip class="item" effect="dark" content="Añadir Nota" placement="top-start">
                                                                                <el-button type="primary" icon="el-icon-document"> </el-button>
                                                                            </el-tooltip>
                                                                            <el-tooltip class="item" effect="dark" content="Borrar item" placement="top-start">
                                                                                <el-button type="danger" icon="el-icon-delete" @click="borrarItem(index,item)"> </el-button>
                                                                            </el-tooltip>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                    <p style="font-size: 2em"><b style="color:blue">TOTAL:</b> <b style="color:darkgreen">{{total}}</b></p>
                                                <div class="text-center">
                                                    <table class="table">
                                                        <tr>
                                                            <td style="width: 50%"><el-button size="small" icon="el-icon-s-order" class="btn-block" @click="precuenta">Precuenta</el-button></td>
                                                            <td style="width: 50%"><el-button size="small" icon="el-icon-dish" class="btn-block" @click="enviar_comanda">Enviar Comanda</el-button></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <el-button v-if="pedidoId>0  && pedidos_detalles.length>0" type="primary" icon="el-icon-save" @click="finalizarVenta"> Finalizar Venta</el-button>
                                                    <el-button v-else type="danger" icon="el-icon-plus" @click="abrirMesa"> Abrir Mesa</el-button>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </el-tab-pane>


                        </el-tabs>
                    </div>
                    <div v-else-if="AqpTap.active==3" class="col-md-12">
                        <tenant-restaurant-pedidos></tenant-restaurant-pedidos>
                    </div>
                    <div v-else-if="AqpTap.active==4" class="col-md-12">
                        <tenant-restaurant-precios></tenant-restaurant-precios>
                    </div>

                </div>
            </div>

            <tenant-restaurant-pedidos-options :showDialogOptions.sync="showDialogOptions"
                               :recordId.sync="pedidoId"
                               :showGenerate="true"
                               :showClose="true"
                               :items="pedidos_detalles"
                               :id_user2="id_user2"
                               :type-user="type_user"
                               :mesa_id="mesaActivo.id"
                               :mesaIsActivo.sync="mesaIsActivo"
                                @onLimPiarDatos="onLimPiarDatos"
                               :configuration="configuration"></tenant-restaurant-pedidos-options>
        </div>
    </div>
</template>
<script>
import tenantRestaurantPedidosOptions from './partials/options.vue'
import tenantRestaurantPrecios from './taps/precios.vue'
import tenantRestaurantPedidos from './taps/pedidos.vue'
import SocketClient from '@mixins/socket.js'

export default {
    mixins:[SocketClient],
    components: {
        tenantRestaurantPedidosOptions,
        tenantRestaurantPrecios,
        tenantRestaurantPedidos
    },
    props: {
        configuracionSocket:{
            type: Object,
            default: () => ({})
        },
        configuration: {
            type: Array,
            required: true,
            default: [],
        },
        items: {
            type: Array,
            required: true,
        },
        id_user2:{
            type: Object,
            required: true,
        },
        type_user:{
            type: String,
            required: true,
        },

    }, //'typeUser'
    data() {
        return {
            showDialogOptions:false,
            loading : false,
            resource: "restaurant",
            recordId: null,
            AqpTap:{
                active:2 //mesas
                //active:4 //precios
            },
            activeName: '',
            mesas:[],
            mesaActivo: {},
            mesaIsActivo:false,
            niveles:[],
            vistaMesas:null,
            pedidos_detalles:[
                // {
                //     id:null,
                //     cant:2,
                //     price:34.66,
                //     description:'papas fritas'
                // }
            ],
            total:0,
            pedidoId:0
        };
    },
    created() {
        this.handleClick()
        this.vistaMesas = true

        //console.log(this.items)
        this.initSocket();
    },
    methods: {
        initSocket(){

            if(!this.configuracionSocket) return

            if(!this.configuracionSocket.active) return;

            try{

                const protocol = window.location.protocol;

                const { domain, port } = this.configuracionSocket;

                const { Manager } = this.io;

                const url = `${protocol}//${domain}:${port}`;

                const manager = new Manager(`${url}`);

                this.socketClient = manager.socket("/");

                this.socketClient.on('mesa-ocupada', (params) => {
                    this.onUpdateItem();
                });


            }catch(error){
                this.socketClient = null;
            }

        },
        notificationAll(){
            if(this.socketClient) this.socketClient.emit('mesa-ocupada',true);
        },
        async onUpdateItem(){
            console.log("socket jugando")

        },
        cargarTap(id){
            this.AqpTap.active=id
            this.vistaMesas=true
            this.onLimPiarDatos()
        },
        handleClick(tab, event) {
            //console.log(tab, event);
            this.onLimPiarDatos()
            this.cargarNiveles(tab)

        },
        async cargarNiveles(tab){
            try{
                this.loading = true;
                const { data } = await this.$http.get(`/restaurant/niveles/records`);
                this.loading = false;
                this.niveles = data.data;
                this.activeName= (this.niveles.length>0 && tab ==null) ? this.niveles[0].nombre : tab.name
                this.vistaMesas=true


            }catch(error){
                this.loading = false;
                if(error.response) this.axiosError(error);

            }
        },
        seleccionaMesa(mesa){
            this.vistaMesas=false
            this.mesaActivo=mesa
            this.mesaIsActivo=true

            this.verificarEstadoMesa(mesa)
        },
        agregarItem(item){
            let exist = this.checkIfExists(item.id)
            if(exist){
                this.pedidos_detalles.find(item2 => item2.producto_id === item.id).cantidad +=1
            }else{
                this.pedidos_detalles.push({producto_id: item.id, cantidad: 1, precio: item.sale_unit_price , descripcion: item.name});
            }

            this.calculateTotal()
        },
        disminuirCantidad(item){

            if(item.cantidad==1){
                return
            }else {
                this.pedidos_detalles.find(item2 => item2.producto_id === item.id).cantidad -=1
            }

            this.calculateTotal()
        },
        incrementarCantidad(item){
            this.pedidos_detalles.find(item2 => item2.producto_id === item.id).cantidad +=1
            this.calculateTotal()
        },
        borrarItem(index,item){
            if(this.pedidoId>0){
                let data = {
                    pedido_id : this.pedidoId,
                    item_id: item.id
                }
                this.loading = true;
                this.$http
                    .put(`/restaurant/cash/sales/item/delete_item`, data)
                    .then((response) => {
                        this.cargarPedidosDetalle(this.pedidoId)
                    })
                    .finally(() => {
                        this.loading = false;
                        this.errors = {};
                    })
                    .catch((error) => {
                        this.axiosError(error);
                    });
            }else{
                this.pedidos_detalles.splice(index, 1);
            }
            this.calculateTotal()
        },
        checkIfExists(itemId) {
            return this.pedidos_detalles.find(item2 => item2.producto_id === itemId)
        },
        calculateTotal() {
            let total = 0;

            this.pedidos_detalles.forEach(row => {

                // isc
                total += parseFloat(row.cantidad*row.precio)

                this.total=total

            });
        },
        abrirMesa(){

            let data = {
                pedido: {
                    mesa_id:this.mesaActivo.id,
                    estado_mesa:'1',
                    tipo:'mesa',
                    pedidos_detalle:this.pedidos_detalles
                },
            }

            this.loading = true;
            this.$http
                .post("/restaurant/cash/sales/store", data)
                .then((response) => {

                    this.$notify({
                        title: '',
                        message: 'Pedido guardado...',
                        type: 'success'
                    })

                    this.onLimPiarDatos()

                    this.pedidoId = response.data.data.id
                    this.cargarPedidosDetalle(this.pedidoId)

                })
                .finally(() => {
                    this.loading = false;
                    this.errors = {};
                })
                .catch((error) => {
                    this.axiosError(error);
                });
        },
        onLimPiarDatos(){
            //this.pedidoId=null
            this.pedidos_detalles=[]
            this.total=0
        },
       async cargarPedidosDetalle(pedido_id){
            try{
                this.loading = true;
                const { data } = await this.$http.get(`/restaurant/cash/sales/get-pedidos-detalles/${pedido_id}`);
                this.loading = false;
                this.pedidos_detalles = data.data;

                this.calculateTotal()

            }catch(error){
                this.loading = false;
                if(error.response) this.axiosError(error);
            }
        },
       async verificarEstadoMesa(mesa){
            try{

                this.loading = true;

                await this.$http.post(`/restaurant/cash/sales/estado-mesa`, {
                    'mesa_id': mesa.id,
                    'nivel_id': mesa.nivel_id
                })
                .then(data => {
                    this.loading = false;
                    this.pedidoId = data.data.pedidoId;
                    if(this.pedidoId>0){
                        this.cargarPedidosDetalle(this.pedidoId)
                    }else{
                        this.onLimPiarDatos()
                    }
                });

            }catch(error){
                this.loading = false;
                if(error.response) this.axiosError(error);
                console.log(error)
            }
       },
       controlarEstadosMesas(mesa){
            let bg_estado = "#00C040"

           if(mesa.estado=="0"){
               bg_estado="#00C040"
           }else if (mesa.estado=="1"){
               bg_estado="#ff0000"
           }
            return bg_estado
       },

       finalizarVenta(pedidoId = null){
               //this.pedidoId = pedidoId
               this.showDialogOptions = true

       },
        precuenta(){
            alert("precuenta")
        },
        enviar_comanda(){
            alert("comanda")
        }
    }
}
</script>
