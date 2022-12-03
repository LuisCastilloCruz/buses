<template>
    <div class="row mt-5">
        <div class="col-xl-4 col-md-4 col-sm-12 offset-1" v-loading="loading">
            <div class="row">
                <el-card v-for="item in preparacion" :key="item.id" class="box-card col-md-12 mt-4">
                    <div slot="header" class="clearfix">
                        <span>Pedido {{ item.order_id }}</span>
                        <el-button style="float: right; padding: 3px 0" type="text">Operation button</el-button>
                    </div>
                    <div class="text item">
                        <b>TOTAL: S/ {{item.total}}}</b>
                    </div>
                </el-card>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 col-sm-12 offset-1" v-loading="loading">
            <div class="row">
                <el-card v-for="item in preparacion" :key="item.id" class="box-card col-md-12 mt-4">
                    <div slot="header" class="clearfix">
                        <span>Pedido {{ item.order_id }}</span>
                        <el-button style="float: right; padding: 3px 0" type="text">Operation button</el-button>
                    </div>
                    <div class="text item">
                        <b>TOTAL: S/ {{item.total}}}</b>
                    </div>
                </el-card>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 col-sm-12 offset-1" v-loading="loading">
            <div class="row">
                <el-card v-for="item in preparacion" :key="item.id" class="box-card col-md-12 mt-4">
                    <div slot="header" class="clearfix">
                        <span>Pedido {{ item.order_id }}</span>
                        <el-button style="float: right; padding: 3px 0" type="text">Operation button</el-button>
                    </div>
                    <div class="text item">
                        <b>TOTAL: S/ {{item.total}}}</b>
                    </div>
                </el-card>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            loading :false,
            tableData: [],
            preparacion:[],
            enviado:[],
            entregado:[],
            search: '',
            visible:false,
            item: {}
        }
    },
    created() {
        this.getPedidos()
    },
    methods: {

        async getPedidos(){
            try{
                this.loading = true;
                const { data } = await this.$http.get(`/restaurant/taps/pedidos`);
                this.loading = false;
                console.log(data.data)
                this.preparacion = data.data;
                this.loading = false;

            }catch(error){
                this.loading = false;
                if(error.response) this.axiosError(error);

            }
        }

    },
}
</script>
