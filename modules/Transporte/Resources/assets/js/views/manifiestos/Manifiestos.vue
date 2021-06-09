<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>REGISTRO DE MANIFIESTOS</span></li>
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
                <h3 class="my-0">Listado de manifiestos</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <template v-if="listManifiestos.length > 0">
                            <thead>
                            <tr>
                                <th>Serie</th>
                                <th>Número</th>
                                <th>Chofer</th>
                                <th>Copiloto</th>
                                <th>Fecha salida</th>
                                <th>Hora salida</th>
                                <th>Observaciones</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="manifiesto in listManifiestos" :key="manifiesto.id">
                                <td>{{ manifiesto.serie.number }}</td>
                                <td>{{ manifiesto.numero }}</td>
                                <td>{{ manifiesto.chofer.nombre }}</td>
                                <td>{{ manifiesto.copiloto.nombre }}</td>
                                <td>{{ manifiesto.fecha }}</td>
                                <td>{{ manifiesto.hora }}</td>
                                <td>{{ manifiesto.observaciones }}</td>
                                <td class="text-center">


                                    <!-- <el-button type="success" @click="onEdit(encomienda)">
                                        <i class="fa fa-edit"></i>
                                    </el-button> -->
                                    <el-button type="primary" @click="imprimir(manifiesto)">
                                        <i class="fa fa-file-alt"></i>
                                    </el-button>
                                    <!-- <el-button type="danger" @click="onDelete(encomienda)">
                                        <i class="fa fa-trash"></i>
                                    </el-button> -->
                                </td>
                            </tr>

                        </tbody>
                        </template>
                        <template v-else>
                            <tr>
                                <td class="text-center" colspan="8">
                                   <el-alert
                                    center
                                    title="No hay manifiestos registrados"
                                    type="info"
                                    :closable="false">
                                    </el-alert>
                                </td>
                            </tr>
                        </template>

                    </table>
                </div>
            </div>
        </div>

        <add-edit :visible.sync="visible" :series="series"></add-edit>
    </div>
</template>
<script>
import AddEdit from './AddEdit';
export default {
    props:{
        listManifiestos:{
            type:Array,
            default:() => []
        },
        series:{
            type:Array,
            default:() => []
        }
    },
    components:{
        AddEdit
    },
    data(){
        return({
            visible:false,
        });
    },
    methods:{
        onCreate(){
            this.visible = false;
        },
        imprimir(manifiesto){
            window.open(`/transportes/manifiestos/${manifiesto.id}/imprimir-manifiesto`);
        }
    }

}
</script>
