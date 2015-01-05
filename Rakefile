# Redefine system to exit with nonzero status on fail.
def system(*args)
  super(*args) or exit!(1)
end

desc 'Generate deck from Travis CI and publish to GitHub Pages.'
task :travis do
  # if this is a pull request, do a simple build of the site and stop
  if ENV['TRAVIS_PULL_REQUEST'].to_s.to_i > 0
    puts 'Pull request detected.'
    next
  end

  repo = %x(git config remote.origin.url).gsub(/^git:/, 'https:').strip
  deploy_url = repo.gsub %r{https://}, "https://#{ENV['GH_TOKEN']}@"
  deploy_branch = repo.match(/github\.io\.git$/) ? 'master' : 'gh-pages'
  rev = %x(git rev-parse HEAD).strip

  system "git clone --branch #{deploy_branch} #{repo}"
  system 'apigen generate'
  Dir.chdir dir do
    # setup credentials so Travis CI can push to GitHub
    system "git config user.name '#{ENV['GIT_NAME']}'"
    system "git config user.email '#{ENV['GIT_EMAIL']}'"

    system 'git add --all'
    system "git commit -m 'Built from #{rev}'."
    system "git push -q #{deploy_url} #{deploy_branch}"
  end
end