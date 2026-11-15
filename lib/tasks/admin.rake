require "io/console"

namespace :admin do
  desc "Create the admin login, or change its password if it exists"
  task user: :environment do
    print "Email address: "
    email = $stdin.gets.to_s.strip
    print "Password: "
    password = $stdin.noecho(&:gets).to_s.chomp
    puts

    user = User.find_or_initialize_by(email_address: email)
    user.password = password
    if user.save
      user.sessions.destroy_all # a new password logs out every device
      puts "Saved. Log in at /admin with #{user.email_address}."
    else
      abort user.errors.full_messages.to_sentence
    end
  end
end
