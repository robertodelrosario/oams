<template>
    <form @submit.prevent="submit">


        <div style="width: 430px; margin-left: 20px">
            <label for="parameter">PARAMETER A:</label>
            <input
                id="parameter"
                type="text"
                name="parameter"
                placeholder="Enter parameter title"
                class="form-control form-control-md"
            >
        </div>
        <table>
            <tr>
                <div style="margin-left: 20px">
                    <th>
                        <div style="margin-top: 10px">
                            <div class="form-group">
                                <label for="system-input">System-Input Process</label>
                                <table>
                                    <div v-for="row in rowsSI" :key="row.id_SI" >
                                        <tr>
                                            <th>
                                                <input type="text"
                                                       id="system-input"
                                                       v-model="row.key"
                                                       placeholder="Statement"
                                                       class="form-control form-control-md"
                                                       style="width: 400px"
                                                >
                                            </th>
                                            <th>
                                                <div class="d-flex justify-content-center">
                                                    <button @click.prevent="addFormSI(row.key)" class="btn bnt-lg btn-outline-success" >
                                                        +
                                                    </button>
                                                </div>
                                            </th>
                                        </tr>
                                    </div>
                                </table>

                            </div>
                            <div class="d-flex justify-content-center">
                                <a @click.prevent="addFormSI" class="btn bnt-lg btn-outline-success">
                                    Add
                                </a>
                            </div>
                        </div>
                    </th>

                    <th>
                        <div style="margin-top: 10px">
                            <div class="form-group">
                                <label for="implementation">Implementation</label>
                                <table>
                                    <div v-for="row in rowsImp" :key="row.id_Imp" >
                                        <tr>
                                            <th>
                                                <input type="text"
                                                       id="implementation"
                                                       v-model="row.key"
                                                       placeholder="Statement"
                                                       class="form-control form-control-md"
                                                       style="width: 400px"
                                                >
                                            </th>
                                            <th>
                                                <div class="d-flex justify-content-center">
                                                    <a @click.prevent="addFormImp" class="btn bnt-lg btn-outline-success">
                                                        +
                                                    </a>
                                                </div>
                                            </th>
                                        </tr>
                                    </div>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center">
                                <a @click.prevent="addFormImp" class="btn bnt-lg btn-outline-success">
                                    Add
                                </a>
                            </div>
                        </div>
                    </th>

                    <th>
                        <div style="margin-top: 10px">
                            <div class="form-group">
                                <label for="outcome">Outcome</label>
                                <table>
                                    <div v-for="row in rowsOut" :key="row.id_Out" >
                                        <tr>
                                            <th>
                                                <input type="text"
                                                       id="outcome"
                                                       v-model="row.key"
                                                       placeholder="Statement"
                                                       class="form-control form-control-md"
                                                       style="width: 400px"
                                                >
                                            </th>
                                            <th>
                                                <div class="d-flex justify-content-center">
                                                    <a @click.prevent="addFormOut" class="btn bnt-lg btn-outline-success">
                                                        +
                                                    </a>
                                                </div>
                                            </th>
                                        </tr>
                                    </div>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center">
                                <a @click.prevent="addFormOut" class="btn bnt-lg btn-outline-success">
                                    Add
                                </a>
                            </div>
                        </div>
                    </th>
                </div>
            </tr>

        </table>


        <button style="margin-left: 20px; margin-top: 50px" type="submit" class="btn btn-primary">Add Parameter</button>
    </form>
</template>



<script>
export default {
    data() {
        return {
            fields: {},
            errors: {},
            id_SI: 0,
            rowsSI: [{ id_SI: 0, value: "" }],
            id_Imp: 0,
            rowsImp: [{ id_Imp: 0, key: "", value: "" }],
            id_Out: 0,
            rowsOut: [{ id_Out: 0, key: "", value: "" }],
        }
    },
    methods: {
        submit() {
            this.errors = {};
            axios.post('/submit', this.fields).then(() => {
                alert('Message sent!');
            }).catch(error => {
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },

        addFormSI: function() {
            this.id_SI = this.id_SI + 1;
            this.rowsSI.push({ id_SI: this.id_SI, value: "" });
        },
        addFormImp: function() {
            this.id_Imp = this.id_Imp + 1;
            this.rowsImp.push({ id_Imp: this.id_Imp, key: "", value: "" });
        },
        addFormOut: function() {
            this.id_Out = this.id_Out + 1;
            this.rowsOut.push({ id_Out: this.id_Out, key: "", value: "" });
        },
    },
}
</script>
