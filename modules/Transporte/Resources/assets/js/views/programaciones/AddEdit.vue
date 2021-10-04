<template>
    <el-dialog
        :title="title"
        :visible="visible"
        @close="onClose"
        @open="onCreate"
        width="50%"
    >
        <form autocomplete="off" @submit.prevent="onSubmit">
            <div class="form-body">
                <div class="form-group">
                    <label for="nombre">Origen</label>
                    <el-select placeholder="Seleccionar origen" v-model="form.terminal_origen_id" :class="{ 'is-invalid': errors.destino_id}" >
                        <el-option
                            v-for="terminal in terminales"
                            :key="terminal.id"
                            :value="terminal.id"
                            :label="terminal.nombre"
                        ></el-option>
                    </el-select>
                    <!-- <input type="text" name="nombre" id="nombre" class="form-control" v-model="form.terminal_origen_id" :class="{ 'is-invalid': errors.terminal_origen_id }"/> -->
                    <div v-if="errors.terminal_origen_id" class="invalid-feedback">{{ errors.terminal_origen_id[0] }}</div>
                </div>
                <div v-if="form.terminal_origen_id" class="form-group">
                    <label for="direccion">Destino</label>
                    <el-select placeholder="Seleccionar destino" v-model="form.terminal_destino_id" :class="{ 'is-invalid': errors.terminal_destino_id}">
                        <template v-for="terminal in terminales">
                            <el-option
                                v-if="terminal.id != form.terminal_origen_id"
                                :key="terminal.id"
                                :value="terminal.id"
                                :label="terminal.nombre"
                            ></el-option>
                        </template>
                        
                    </el-select>
                    <div v-if="errors.terminal_destino_id" class="invalid-feedback">{{ errors.terminal_destino_id[0] }}</div>
                </div>
                <div class="form-group">
                    <label for="direccion">Vehiculo</label>
                    <el-select placeholder="Seleccionar destino" v-model="form.vehiculo_id" :class="{ 'is-invalid': errors.vehiculo_id}">
                        <template v-for="vehiculo in vehiculos">
                            <el-option
                                :key="vehiculo.id"
                                :value="vehiculo.id"
                                :label="vehiculo.placa"
                            ></el-option>
                        </template>
                        
                    </el-select>
                    <div v-if="errors.vehiculo_id" class="invalid-feedback">{{ errors.vehiculo_id[0] }}</div>
                </div>
                
                <div class="form-group">
                    <label for="hora_salida">Hora salida</label>
                    <el-input type="time" v-model="form.hora_salida" placeholder="Hora Salida" ></el-input>
                    <!-- <el-time-picker v-model="form.hora_salida" placeholder="Hora Salida" value-format="H:m:s">
                    </el-time-picker> -->
                    <!-- <el-input
                        type="time"
                        
                        
                    /> -->
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="" class="control-label">Seleccionar Ruta</label>
                            <el-select v-model="selectTerminal" filterable remote value-key="id" popper-class="el-select-customers"
                                placeholder="Buscar origen"
                                :remote-method="getTerminales"
                                :loading="loadingTerminales"
                            >
                                <template v-for="terminal in listTerminales">
                                    <el-option  v-if="terminal.id != form.terminal_origen_id && terminal.id != form.terminal_destino_id" :key="terminal.id" :value="terminal" :label="terminal.nombre">
                                    </el-option>
                                   
                                </template>
                            </el-select>
                                

                        </div>
                    </div>
                    <div class="col-md-6">
                        <el-button :style="{marginTop:'1.95rem'}" @click="addTerminal">Agregar</el-button>
                    </div>
                </div>

                <draggable v-model="selectTerminales">
                    <template v-for="(element,index) in selectTerminales" >
                        <div :key="element.id" class="mt-2">
                            <el-card>
                                <div class="row justify-content-center">
                                    <div class="col-md-4">
                                        {{element.nombre}}
                                    </div>
                                    <div class="col-md-4">
                                        <el-input type="time" v-model="element.hora_salida" placeholder="Hora"></el-input>
                                    </div>
                                    <div class="col-md-4">
                                        <el-button type="danger" @click="onDelete(element,index)">Eliminar</el-button>
                                    </div>
                                </div>
                            </el-card>
                        </div>
                    </template>
                   
                    
                
                </draggable>


                
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <el-button
                            native-type="submit"
                            :disabled="loading"
                            type="primary"
                            class="btn-block"
                            :loading="loading"
                        >Guardar</el-button
                        >
                    </div>
                    <div class="col-6">
                        <el-button class="btn-block" @click="onClose">Cancelar</el-button>
                    </div>
                </div>
            </div>
        </form>
    </el-dialog>
</template>

<script>
import Draggable from 'vuedraggable';
export default {
    components:{ Draggable },
    props: {
        visible: {
            type: Boolean,
            required: true,
            default: false,
        },
        programacion: {
            type: Object,
            required: false,
            default: {},
        },
        terminales:{
            type:Array,
            default:() => []
        },
        vehiculos:{
            type:Array,
            default:() => []
        },
        userTerminal:{
            type:Object,
            default:{}
        }
    },
    created(){
        this.form.terminal_origen_id = this.userTerminal.terminal_id;
        this.getTerminales()
    },
    data() {
        return {
            form: {
                terminal_origen_id:null,
                terminal_destino_id:null,
                // tiempo_aproximado:null,
                hora_salida:null,
                vehiculo_id:null
            },
            title: "",
            errors: {},
            loading: false,
            listTerminales:[],
            selectTerminal:null,
            loadingTerminales:false,
            selectTerminales:[],
            onEdit:false,
        };
    },
    watch:{

        'form.vehiculo_id'(){
            this.selectTerminales.map(  terminal => {
                terminal.vehiculo_id = this.form.vehiculo_id;
                return terminal;
            })

        }

    },
    methods: {

        async getTerminales(input = ''){
            try{
                this.loadingTerminales = true;
                const {data} = await this.$http.get(`/transportes/programaciones/get-terminales?search=${input}`);
                this.loadingTerminales = false;
                this.listTerminales = data.data
            }catch(error){
                this.axiosError(error);
            }
            
        },
        async onUpdate() {
            this.loading = true;
            try{

                let data = {
                    programacion:this.form,
                    intermedios: this.selectTerminales
                };

                const { data:response } =  await this.$http.put(`/transportes/programaciones/${this.programacion.id}/update`,data);
                this.$emit("onUpdateItem", response.data);
                this.onClose();
                this.onEdit = false;
            }catch(error){
                this.axiosError(error);
            }finally{
                this.loading = false;
                this.errors = {};

            }

            // this.$http
            //     .put(`/transportes/terminal/${this.chofer.id}/update`, this.form)
            //     .then((response) => {
            //         this.$emit("onUpdateItem", response.data.data);
            //         this.onClose();
            //     })
            //     .finally(() => {
            //         this.loading = false;
            //         this.errors = {};
            //     })
            //     .catch((error) => {
            //         this.axiosError(error);
            //     });
        },
        async onStore() {
            this.loading = true;
            try{
                let data = {
                    programacion:this.form,
                    intermedios: this.selectTerminales
                }
                const { data:response } = await this.$http.post("/transportes/programaciones/store",data);
                this.$emit("onAddItem", response.data);
                this.onClose();
            } catch(error){
                this.axiosError(error);
            }finally{
                this.loading = false;
                this.errors = {};

            }
            
            // this.$http
            //     .post("/transportes/terminales/store", this.form)
            //     .then((response) => {
            //         this.$emit("onAddItem", response.data.data);
            //         this.onClose();
            //     })
            //     .finally(() => {
            //         this.loading = false;
            //         this.errors = {};
            //     })
            //     .catch((error) => {
            //         this.axiosError(error);
            //     });
        },
        onSubmit() {
            if (this.programacion) {
                this.onUpdate();
            } else {
                this.onStore();
            }
        },
        onClose() {
            this.$emit("update:visible", false);
        },
        onCreate() {
            if (this.programacion) {
                this.onEdit = true;
                this.form = this.programacion;
                this.selectTerminales = this.mapRutas(this.programacion)
                this.title = "Editar programación";
            } else {
                this.title = "Crear programación";
                this.form = {
                    terminal_origen_id:this.userTerminal.terminal_id,
                    terminal_destino_id:null,
                    hora_salida:null,
                    vehiculo_id:null
                };
            }
        },
        addTerminal(){

            let find = this.selectTerminales.find( terminal => terminal.terminal_origen_id == this.selectTerminal.id );

            if(find){
                return this.$message({
                    type:'info',
                    message:'La terminal ya se encuentra agregada'
                });
            }


            let ruta = {
                nombre:this.selectTerminal.nombre,
                terminal_origen_id: this.selectTerminal.id,
                hora_salida: null,
                vehiculo_id:this.form.vehiculo_id
            };

            this.selectTerminales.push(ruta);

            this.selectTerminal = null;
        },
        mapRutas(programacion){
            if(!programacion.rutas) return [];
            return programacion.rutas.map( terminal => {
                return {
                    terminal_origen_id:terminal.id,
                    nombre: terminal.nombre,
                    hora_salida: terminal.pivot.hora_salida,
                }
            });
        },
        async onDelete(terminal,index){


            if(!this.onEdit){

                this.selectTerminales.splice(index,1);

                return this.$message({
                    type: 'success',
                    message: 'Se ha eliminado correctamente'
                });

            }

            this.$confirm(`¿Estás seguro de eliminar la terminal ?`, 'Atención', {
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'No, cerrar',
                type: 'warning'
            }).then(async() => {

                const { data } = await axios.delete(`/transportes/programaciones/${this.programacion.id}/${terminal.id}/delete`);

                if(!data.status) return this.$message({
                    type:'error',
                    message: data.msg
                });

                this.$message({
                    type: 'success',
                    message: data.message
                });

            }).catch((error) => {
                this.axiosError(error);
            });

        }
    },
};
</script>
