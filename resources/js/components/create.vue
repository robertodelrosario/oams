<template>
    <form @submit.prevent="submit">
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
                                    <a @click.prevent="addFormSI" class="btn bnt-lg btn-outline-success" >
                                        +
                                    </a>
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
    },
}
</script>
