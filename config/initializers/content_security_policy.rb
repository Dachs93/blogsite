# The site sends no JavaScript, so scripts are blocked entirely.
Rails.application.configure do
  config.content_security_policy do |policy|
    policy.default_src :self
    policy.img_src :self, :https, :data
    policy.script_src :self
    policy.object_src :none
    policy.base_uri :none
    policy.form_action :self
    policy.frame_ancestors :none
  end
end
