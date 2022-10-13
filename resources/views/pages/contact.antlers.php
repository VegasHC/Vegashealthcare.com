


<div class="py-12 mx-auto prose prose-xl">



    <h1 class="my-4 text-primary">{{ title }}</h1>

    <div class="my-4">



        {{ form:contact_us id="contact-form" }}


            {{ if success }}
                  <div class="p-8 text-xl text-center text-green">
		Thank you! We'll be in touch soon.
            </div>
            {{ else }}

            {{ if errors }}
            <div class="p-2 text-white bg-red-300">
                {{ errors }}
                {{ value }}<br>
                {{ /errors }}
            </div>
            {{ /if }}

            {{ fields }}
                <div class="p-2">
                    <label class="font-bold">{{ display }}</label>
                    <div class="p-1">
                        {{ field }}
                        <span class="text-sm text-gray-500">{{ instructions }}</span>
                    </div>

                    {{ if error }}
                        <p class="text-gray-500">{{ error }}</p>
                    {{ /if }}
                </div>
            {{ /fields }}

            <input type="text" name="address" class="h-px opacity-01">
            <button type="submit" class="block px-20 py-2 mx-auto text-lg font-bold text-white rounded-lg bg-brand hover:bg-brand ">Submit</button>
            {{ /if }}

        {{ /form:contact_us }}


        {{ content }}

    </div>




</div>
