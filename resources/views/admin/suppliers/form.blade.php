<div class="field">
    <label for="name">Supplier Name</label>
    <input id="name" name="name" value="{{ old('name', $supplier->name ?? '') }}" maxlength="255" required>
</div>
<div class="field">
    <label for="contact_person">Contact Person</label>
    <input id="contact_person" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}" maxlength="255">
</div>
<div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email', $supplier->email ?? '') }}" maxlength="255">
</div>
<div class="field">
    <label for="phone">Phone</label>
    <input id="phone" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" maxlength="30">
</div>
<div class="field">
    <label for="address">Address</label>
    <textarea id="address" name="address" maxlength="2000">{{ old('address', $supplier->address ?? '') }}</textarea>
</div>
<div class="field">
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" maxlength="2000">{{ old('notes', $supplier->notes ?? '') }}</textarea>
</div>
<div class="field">
    <input type="hidden" name="is_active" value="0">
    <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true))> Active supplier</label>
</div>
