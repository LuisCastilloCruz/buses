<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" :class="{'has-danger': errors.name}">
                            <label class="control-label">Nombre</label>
                            <el-input v-model="form.name"></el-input>
                            <small class="form-control-feedback" v-if="errors.name" v-text="errors.name[0]"></small>
                        </div>
                        <div v-if="isActiveBussinessTurn('restaurant')" class="form-group" :class="{'has-danger': errors.printer}">
                            <label class="control-label">Impresora</label>
                            <el-input v-model="form.printer"></el-input>
                            <small class="form-control-feedback" v-if="errors.printer" v-text="errors.printer[0]"></small>
                        </div>
                    </div> 
                </div> 
            </div>
            <div class="form-actions text-right pt-2">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
            </div>
        </form>
    </el-dialog>
</template>
 
<script type="text/babel">
 

    export default {
        props: ['showDialog', 'recordId'],
        data() {
            return {
                loading_submit: false,
                titleDialog: null,
                resource: 'categories', 
                errors: {}, 
                form: {},
                business_turns: []
            }
        },
        created() {
            this.initForm() 
        },
        methods: {
            initForm() { 
                this.errors = {} 

                this.form = {
                    id: null,
                    name: null,
                    printer:null
                }
            },
            async create() {

                this.titleDialog = (this.recordId)? 'Editar categoría':'Nueva categoría'
                await this.$http.get(`/documents/tables`)
                    .then(response => {
                        this.business_turns = response.data.business_turns
                    })

                this.loading_form = true
                if (this.recordId) {
                    await this.$http.get(`/${this.resource}/record/${this.recordId}`).then(response => {
                            this.form = response.data
                    })
                }
            },
            submit() {   
 

                this.loading_submit = true  
                this.$http.post(`${this.resource}`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.$eventHub.$emit('reloadData')
                            this.close()
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data 
                        } else {
                            console.log(error.response)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })
                    
            },  
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
            },
            isActiveBussinessTurn(value){
                return (_.find(this.business_turns,{'value':value})) ? true:false
            }
        }
    }
</script>