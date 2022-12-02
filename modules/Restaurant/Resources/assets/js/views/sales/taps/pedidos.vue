<template>
    <div class="row mt-5">
        <div class="col-xl-8 col-md-8 col-sm-12 offset-2" v-loading="loading">
            <el-card class="box-card">
                <div slot="header" class="clearfix">
                    <span>Pedido 001</span>
                    <el-button style="float: right; padding: 3px 0" type="text">Operation button</el-button>
                </div>
                <div v-for="o in 4" :key="o" class="text item">
                    {{'List item ' + o }}
                </div>
            </el-card>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            loading :false,
            tableData: [],
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
                this.tableData = data.data;
                this.loading = false;

            }catch(error){
                this.loading = false;
                if(error.response) this.axiosError(error);

            }
        }

    },
}
</script>
